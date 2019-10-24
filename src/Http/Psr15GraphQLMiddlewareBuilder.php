<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Http;

use GraphQL\Error\Debug;
use GraphQL\Server\ServerConfig;
use GraphQL\Type\Schema;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\MiddlewareInterface;
use TheCodingMachine\GraphQLite\Context\Context;
use TheCodingMachine\GraphQLite\Exceptions\WebonyxErrorHandler;
use TheCodingMachine\GraphQLite\GraphQLRuntimeException;
use Zend\Diactoros\ResponseFactory;
use Zend\Diactoros\StreamFactory;
use function class_exists;

/**
 * A factory generating a PSR-15 middleware tailored for GraphQLite.
 */
class Psr15GraphQLMiddlewareBuilder
{
    /** @var string */
    private $url = '/graphql';
    /** @var ServerConfig */
    private $config;

    /** @var ResponseFactoryInterface|null */
    private $responseFactory;

    /** @var StreamFactoryInterface|null */
    private $streamFactory;

    /** @var HttpCodeDeciderInterface */
    private $httpCodeDecider;

    public function __construct(Schema $schema)
    {
        $this->config = new ServerConfig();
        $this->config->setSchema($schema);
        $this->config->setDebug(Debug::RETHROW_UNSAFE_EXCEPTIONS);
        $this->config->setErrorFormatter([WebonyxErrorHandler::class, 'errorFormatter']);
        $this->config->setErrorsHandler([WebonyxErrorHandler::class, 'errorHandler']);
        $this->config->setContext(new Context());
        $this->httpCodeDecider = new HttpCodeDecider();
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getConfig(): ServerConfig
    {
        return $this->config;
    }

    public function setConfig(ServerConfig $config): self
    {
        $this->config = $config;

        return $this;
    }

    public function setResponseFactory(ResponseFactoryInterface $responseFactory): self
    {
        $this->responseFactory = $responseFactory;

        return $this;
    }

    public function setStreamFactory(StreamFactoryInterface $streamFactory): self
    {
        $this->streamFactory = $streamFactory;

        return $this;
    }

    public function setHttpCodeDecider(HttpCodeDeciderInterface $httpCodeDecider): self
    {
        $this->httpCodeDecider = $httpCodeDecider;

        return $this;
    }

    public function createMiddleware(): MiddlewareInterface
    {
        if ($this->responseFactory === null && ! class_exists(ResponseFactory::class)) {
            throw new GraphQLRuntimeException('You need to set a ResponseFactory to use the Psr15GraphQLMiddlewareBuilder. Call Psr15GraphQLMiddlewareBuilder::setResponseFactory or try installing zend-diactoros: composer require zendframework/zend-diactoros'); // @codeCoverageIgnore
        }
        $this->responseFactory = $this->responseFactory ?: new ResponseFactory();

        if ($this->streamFactory === null && ! class_exists(StreamFactory::class)) {
            throw new GraphQLRuntimeException('You need to set a StreamFactory to use the Psr15GraphQLMiddlewareBuilder. Call Psr15GraphQLMiddlewareBuilder::setStreamFactory or try installing zend-diactoros: composer require zendframework/zend-diactoros'); // @codeCoverageIgnore
        }
        $this->streamFactory = $this->streamFactory ?: new StreamFactory();

        return new WebonyxGraphqlMiddleware($this->config, $this->responseFactory, $this->streamFactory, $this->httpCodeDecider, $this->url);
    }
}
