<?php

use GraphQL\Type\Schema;
use Mouf\Picotainer\Picotainer;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Simple\FilesystemCache;
use TheCodingMachine\GraphQLite\Http\Psr15GraphQLMiddlewareBuilder;
use TheCodingMachine\GraphQLite\SchemaFactory;
use Laminas\Stratigility\MiddlewarePipe;

// Picotainer is a minimalist PSR-11 container.
return new Picotainer([
    MiddlewarePipe::class => function(ContainerInterface $container) {
        $pipe = new MiddlewarePipe();
        $pipe->pipe($container->get(WebonyxGraphqlMiddleware::class));
        return $pipe;
    },
    // The WebonyxGraphqlMiddleware is a PSR-15 compatible
    // middleware that exposes Webonyx schemas.
    WebonyxGraphqlMiddleware::class => function(ContainerInterface $container) {
        $builder = new Psr15GraphQLMiddlewareBuilder($container->get(Schema::class));
        return $builder->createMiddleware();
    },
    CacheInterface::class => function() {
        return new FilesystemCache();
    },
    Schema::class => function(ContainerInterface $container) {
        // The magic happens here. We create a schema using GraphQLite SchemaFactory.
        $factory = new SchemaFactory($container->get(CacheInterface::class), $container);
        $factory->addControllerNamespace('App\\Controllers');
        $factory->addTypeNamespace('App');
        return $factory->createSchema();
    },
    // We declare the controller in the container.
    MyController::class => function() {
        return new MyController();
    },
]);

