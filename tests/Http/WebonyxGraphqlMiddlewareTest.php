<?php

namespace TheCodingMachine\GraphQLite\Http;

use GraphQL\Executor\ExecutionResult;
use GraphQL\Executor\Promise\Adapter\SyncPromise;
use GraphQL\Executor\Promise\Adapter\SyncPromiseAdapter;
use GraphQL\Executor\Promise\Promise;
use GraphQL\Server\ServerConfig;
use GraphQL\Server\StandardServer;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\TextResponse;
use Zend\Diactoros\ResponseFactory;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\StreamFactory;
use Zend\Diactoros\Uri;
use function fopen;
use function fwrite;
use function json_encode;
use function rewind;

class WebonyxGraphqlMiddlewareTest extends TestCase
{
    public function testProcess() : void
    {
        $handler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request) : ResponseInterface
            {
                return new TextResponse('skipped');
            }
        };
        $standardServer = new class extends StandardServer {
            /** @var ExecutionResult|ExecutionResult[]|Promise */
            private $executionResult;
            public function __construct()
            {
                parent::__construct([]);
            }
            /**
             * @param ExecutionResult|ExecutionResult[]|Promise $executionResult
             */
            public function setExecutionResult($executionResult) : void
            {
                $this->executionResult = $executionResult;
            }

            /**
             * @return ExecutionResult|ExecutionResult[]|Promise
             */
            public function executePsrRequest(ServerRequestInterface $request)
            {
                return $this->executionResult;
            }
        };
        $middleware = new WebonyxGraphqlMiddleware(
            new ServerConfig(),
            new ResponseFactory(),
            new StreamFactory(),
            new HttpCodeDecider(),
            '/graphql',
            $standardServer
        );
        $standardServer->setExecutionResult(new ExecutionResult(['foo']));
        $request = $this->createRequest();
        $this->assertSame('skipped', $middleware->process($request, $handler)->getBody()->getContents());
        $request = $this->createRequest()->withHeader('content-type', 'application/graphql');
        $this->assertSame('{"data":["foo"]}', $middleware->process($request, $handler)->getBody()->getContents());
        $request = $this->createRequest()->withHeader('content-type', 'application/json');
        $this->assertSame('skipped', $middleware->process($request, $handler)->getBody()->getContents());
        $request = $this->createRequest()->withUri(new Uri('/graphql'));
        $this->assertSame('{"data":["foo"]}', $middleware->process($request, $handler)->getBody()->getContents());
        $request = $this->createRequest()->withUri(new Uri('/graphql'));
        $standardServer->setExecutionResult([new ExecutionResult(['foo']), new ExecutionResult(['bar'])]);
        $this->assertSame(
            '[{"data":["foo"]},{"data":["bar"]}]',
            $middleware->process($request, $handler)->getBody()->getContents()
        );
        $syncPromise = new SyncPromise();
        $request = $this->createRequest()->withUri(new Uri('/graphql'));
        $standardServer->setExecutionResult(new Promise($syncPromise, new SyncPromiseAdapter()));
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Only SyncPromiseAdapter is supported');
        $middleware->process($request, $handler);
    }
    private function createRequest() : ServerRequest
    {
        $data        = [
            'query'     => 'query getMatter($id: String!) {\n matter(id: $id) {\nid\n}\n}',
            'variables' => ['id' => '4d967a0f65224f1685a602cbe4eef667'],
        ];
        $jsonContent = json_encode($data);
        $stream      = fopen('php://memory', 'r+');
        fwrite($stream, $jsonContent);
        rewind($stream);
        return new ServerRequest(
            [],
            [],
            null,
            null,
            $stream,
            []
        );
    }
}
