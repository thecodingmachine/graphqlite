<?php

namespace TheCodingMachine\GraphQLite\Http;

use GraphQL\Executor\ExecutionResult;
use GraphQL\Server\ServerConfig;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Component\Cache\Simple\ArrayCache;
use TheCodingMachine\GraphQLite\AggregateQueryProvider;
use TheCodingMachine\GraphQLite\Containers\BasicAutoWiringContainer;
use TheCodingMachine\GraphQLite\Containers\EmptyContainer;
use TheCodingMachine\GraphQLite\Middlewares\FieldMiddlewarePipe;
use TheCodingMachine\GraphQLite\SchemaFactory;
use TheCodingMachine\GraphQLite\Security\VoidAuthenticationService;
use TheCodingMachine\GraphQLite\Security\VoidAuthorizationService;
use Zend\Diactoros\Response\TextResponse;
use Zend\Diactoros\ResponseFactory;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\StreamFactory;
use function fopen;
use function fwrite;
use function json_decode;
use function json_encode;
use function rewind;

class Psr15GraphQLMiddlewareBuilderTest extends TestCase
{

    public function testCreateMiddleware()
    {
        $container = new BasicAutoWiringContainer(new EmptyContainer());
        $cache = new Psr16Cache(new ArrayAdapter());

        $factory = new SchemaFactory($cache, $container);
        $factory->setAuthenticationService(new VoidAuthenticationService());
        $factory->setAuthorizationService(new VoidAuthorizationService());

        $factory->addControllerNamespace('TheCodingMachine\\GraphQLite\\Fixtures\\Integration\\Controllers');
        $factory->addTypeNamespace('TheCodingMachine\\GraphQLite\\Fixtures\\Integration');

        $schema = $factory->createSchema();

        $middlewareBuilder = new Psr15GraphQLMiddlewareBuilder($schema);
        $middlewareBuilder->setConfig($middlewareBuilder->getConfig());
        $middlewareBuilder->setHttpCodeDecider(new HttpCodeDecider());
        $middlewareBuilder->setStreamFactory(new StreamFactory());
        $middlewareBuilder->setResponseFactory(new ResponseFactory());
        $middlewareBuilder->setUrl('/foobar');

        $handler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request) : ResponseInterface
            {
                return new TextResponse('skipped');
            }
        };

        $middleware = $middlewareBuilder->createMiddleware();
        $request = $this->createRequest();
        $content = $middleware->process($request, $handler)->getBody()->getContents();
        $data = json_decode($content, true)['data'];
        $this->assertSame('Joe', $data['contacts'][0]['name']);
    }

    private function createRequest() : ServerRequest
    {
        $data        = [
            'query'     => "{\n contacts {\nname\n}\n}",
            'variables' => ['id' => '4d967a0f65224f1685a602cbe4eef667'],
        ];
        $jsonContent = json_encode($data);
        $stream      = fopen('php://memory', 'rb+');
        fwrite($stream, $jsonContent);
        rewind($stream);
        return new ServerRequest(
            [],
            [],
            '/foobar',
            'POST',
            $stream,
            [
                'content-type' => 'application/json'
            ]
        );
    }
}
