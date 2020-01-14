<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Http;

use GraphQL\Executor\ExecutionResult;
use GraphQL\Executor\Promise\Promise;
use GraphQL\Server\ServerConfig;
use GraphQL\Server\StandardServer;
use InvalidArgumentException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;
use TheCodingMachine\GraphQLite\Context\ResetableContextInterface;
use function array_map;
use function explode;
use function in_array;
use function is_array;
use function json_decode;
use function json_encode;
use function json_last_error;
use function json_last_error_msg;
use function max;
use const JSON_ERROR_NONE;

final class WebonyxGraphqlMiddleware implements MiddlewareInterface
{
    /** @var StandardServer */
    private $standardServer;
    /** @var string */
    private $graphqlUri;
    /** @var string[] */
    private $graphqlHeaderList = ['application/graphql'];
    /** @var string[] */
    private $allowedMethods = [
        'GET',
        'POST',
    ];

    /** @var bool|int */
    //private $debug;
    /** @var ResponseFactoryInterface */
    private $responseFactory;
    /** @var StreamFactoryInterface */
    private $streamFactory;
    /** @var HttpCodeDeciderInterface */
    private $httpCodeDecider;
    /** @var ServerConfig */
    private $config;

    public function __construct(
        ServerConfig $config,
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface $streamFactory,
        HttpCodeDeciderInterface $httpCodeDecider,
        string $graphqlUri = '/graphql',
        ?StandardServer $handler = null
    ) {
        $this->responseFactory = $responseFactory;
        $this->streamFactory   = $streamFactory;
        $this->graphqlUri      = $graphqlUri;
        $this->httpCodeDecider = $httpCodeDecider;
        $this->config = $config;
        $this->standardServer  = $handler ?: new StandardServer($config);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (! $this->isGraphqlRequest($request)) {
            return $handler->handle($request);
        }

        // Let's json deserialize if this is not already done.
        if (empty($request->getParsedBody())) {
            $content = $request->getBody()->getContents();
            $data = json_decode($content, true);

            if ($data === false || json_last_error() !== JSON_ERROR_NONE) {
                throw new InvalidArgumentException(json_last_error_msg() . ' in body: "' . $content . '"'); // @codeCoverageIgnore
            }

            $request = $request->withParsedBody($data);
        }

        $context = $this->config->getContext();
        if ($context instanceof ResetableContextInterface) {
            $context->reset();
        }
        $result = $this->standardServer->executePsrRequest($request);
        //return $this->standardServer->processPsrRequest($request, $this->responseFactory->createResponse(), $this->streamFactory->createStream());

        return $this->getJsonResponse($this->processResult($result), $this->decideHttpCode($result));
    }

    /**
     * @param ExecutionResult|ExecutionResult[]|Promise $result
     *
     * @return mixed[]
     */
    private function processResult($result): array
    {
        if ($result instanceof ExecutionResult) {
            return $result->toArray($this->config->getDebug());
        }

        if (is_array($result)) {
            return array_map(function (ExecutionResult $executionResult) {
                return $executionResult->toArray($this->config->getDebug());
            }, $result);
        }

        if ($result instanceof Promise) {
            throw new RuntimeException('Only SyncPromiseAdapter is supported');
        }

        throw new RuntimeException('Unexpected response from StandardServer::executePsrRequest'); // @codeCoverageIgnore
    }

    /**
     * @param ExecutionResult|ExecutionResult[]|Promise $result
     */
    private function decideHttpCode($result): int
    {
        if ($result instanceof ExecutionResult) {
            return $this->httpCodeDecider->decideHttpStatusCode($result);
        }

        if (is_array($result)) {
            $codes = array_map(function (ExecutionResult $executionResult) {
                return $this->httpCodeDecider->decideHttpStatusCode($executionResult);
            }, $result);

            return max($codes);
        }

        // @codeCoverageIgnoreStart
        // Code unreachable because exceptions will be triggered in processResult first.
        // We keep it for defensive programming purpose
        if ($result instanceof Promise) {
            throw new RuntimeException('Only SyncPromiseAdapter is supported');
        }

        throw new RuntimeException('Unexpected response from StandardServer::executePsrRequest');

        // @codeCoverageIgnoreEnd
    }

    private function isGraphqlRequest(ServerRequestInterface $request): bool
    {
        return $this->isMethodAllowed($request) && ($this->hasUri($request) || $this->hasGraphQLHeader($request));
    }

    private function isMethodAllowed(ServerRequestInterface $request): bool
    {
        return in_array($request->getMethod(), $this->allowedMethods, true);
    }

    private function hasUri(ServerRequestInterface $request): bool
    {
        return $this->graphqlUri === $request->getUri()->getPath();
    }

    private function hasGraphQLHeader(ServerRequestInterface $request): bool
    {
        if (! $request->hasHeader('content-type')) {
            return false;
        }

        $requestHeaderList = array_map('trim', explode(',', $request->getHeaderLine('content-type')));

        foreach ($this->graphqlHeaderList as $allowedHeader) {
            if (in_array($allowedHeader, $requestHeaderList, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param mixed[] $array
     */
    private function getJsonResponse(array $array, int $statusCode): ResponseInterface
    {
        $response = $this->responseFactory->createResponse();
        $data     = json_encode($array);

        if ($data === false || json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException(json_last_error_msg()); // @codeCoverageIgnore
        }

        $stream   = $this->streamFactory->createStream($data);
        $response = $response->withBody($stream)
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($statusCode);

        return $response;
    }
}
