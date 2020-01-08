---
id: other-frameworks
title: Getting started with any framework
sidebar_label: Other frameworks / No framework
---

If you are using **Symfony 4.x**, checkout the [Symfony bundle](symfony-bundle.md).

## Installation

Open a terminal in your current project directory and run:

```console
$ composer require thecodingmachine/graphqlite
```

## Requirements

In order to bootstrap GraphQLite, you will need:

- A PSR-11 compatible container
- A PSR-16 cache

Additionally, you will have to route the HTTP requests to the underlying GraphQL library.

GraphQLite relies on the [webonyx/graphql-php](http://webonyx.github.io/graphql-php/) library internally.
This library plays well with PSR-7 requests and we also provide a [PSR-15 middleware](#psr-15-middleware).

## Integration

Webonyx/graphql-php library requires a [Schema](https://webonyx.github.io/graphql-php/type-system/schema/) in order to resolve
GraphQL queries. We provide a `SchemaFactory` class to create such a schema:

```php
use TheCodingMachine\GraphQLite\SchemaFactory;

// $cache is a PSR-16 compatible cache
// $container is a PSR-11 compatible container
$factory = new SchemaFactory($cache, $container);
$factory->addControllerNamespace('App\\Controllers\\')
        ->addTypeNamespace('App\\');

$schema = $factory->createSchema();
```

You can now use this schema with [Webonyx GraphQL facade](https://webonyx.github.io/graphql-php/getting-started/#hello-world) 
or the [StandardServer class](https://webonyx.github.io/graphql-php/executing-queries/#using-server).

The `SchemaFactory` class also comes with a number of methods that you can use to customize your GraphQLite settings.

```php
// Configure an authentication service (to resolve the @Logged annotations).
$factory->setAuthenticationService(new VoidAuthenticationService());
// Configure an authorization service (to resolve the @Right annotations).
$factory->setAuthorizationService(new VoidAuthorizationService());
// Change the naming convention of GraphQL types globally.
$factory->setNamingStrategy(new NamingStrategy());
// Add a custom type mapper.
$factory->addTypeMapper($typeMapper);
// Add a custom type mapper using a factory to create it.
// Type mapper factories are useful if you need to inject the "recursive type mapper" into your type mapper constructor.
$factory->addTypeMapperFactory($typeMapperFactory);
// Add a root type mapper.
$factory->addRootTypeMapper($rootTypeMapper);
// Add a parameter mapper.
$factory->addParameterMapper($parameterMapper);
// Add a query provider. These are used to find queries and mutations in the application.
$factory->addQueryProvider($queryProvider);
// Add a query provider using a factory to create it.
// Query provider factories are useful if you need to inject the "fields builder" into your query provider constructor.
$factory->addQueryProviderFactory($queryProviderFactory);
// Add custom options to the Webonyx underlying Schema.
$factory->setSchemaConfig($schemaConfig);
// Configures the time-to-live for the GraphQLite cache. Defaults to 2 seconds in dev mode.
$factory->setGlobTtl(2);
// Enables prod-mode (cache settings optimized for best performance).
// This is a shortcut for `$schemaFactory->setGlobTtl(null)`
$factory->prodMode();
// Enables dev-mode (this is the default mode: cache settings optimized for best developer experience).
// This is a shortcut for `$schemaFactory->setGlobTtl(2)`
$factory->devMode();
```

### GraphQLite context

Webonyx allows you pass a "context" object when running a query.
For some GraphQLite features to work (namely: the prefetch feature), GraphQLite needs you to initialize the Webonyx context
with an instance of the `TheCodingMachine\GraphQLite\Context\Context` class.

For instance:

```php
use TheCodingMachine\GraphQLite\Context\Context;

$result = GraphQL::executeQuery($schema, $query, null, new Context(), $variableValues);
```

## Minimal example

The smallest working example using no framework is:

```php
<?php
use GraphQL\GraphQL;
use GraphQL\Type\Schema;
use TheCodingMachine\GraphQLite\SchemaFactory;
use TheCodingMachine\GraphQLite\Context\Context;

// $cache is a PSR-16 compatible cache.
// $container is a PSR-11 compatible container.
$factory = new SchemaFactory($cache, $container);
$factory->addControllerNamespace('App\\Controllers\\')
        ->addTypeNamespace('App\\');

$schema = $factory->createSchema();

$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);
$query = $input['query'];
$variableValues = isset($input['variables']) ? $input['variables'] : null;

$result = GraphQL::executeQuery($schema, $query, null, new Context(), $variableValues);
$output = $result->toArray();

header('Content-Type: application/json');
echo json_encode($output);
```

## PSR-15 Middleware

When using a framework, you will need a way to route your HTTP requests to the `webonyx/graphql-php` library.

If the framework you are using is compatible with PSR-15 (like Slim PHP or Zend-Expressive / Laminas), GraphQLite
comes with a PSR-15 middleware out of the box.

In order to get an instance of this middleware, you can use the `Psr15GraphQLMiddlewareBuilder` builder class:

```php
// $schema is an instance of the GraphQL schema returned by SchemaFactory::createSchema (see previous chapter)
$builder = new Psr15GraphQLMiddlewareBuilder($schema);

$middleware = $builder->createMiddleware();

// You can now inject your middleware in your favorite PSR-15 compatible framework.
// For instance:
$zendMiddlewarePipe->pipe($middleware);
```

The builder offers a number of setters to modify its behaviour:

```php
$builder->setUrl("/graphql"); // Modify the URL endpoint (defaults to /graphql)
$config = $builder->getConfig(); // Returns a Webonyx ServerConfig object. Use this object to configure Webonyx in details.
$builder->setConfig($config);

$builder->setResponseFactory(new ResponseFactory()); // Set a PSR-18 ResponseFactory (not needed if you are using zend-framework/zend-diactoros ^2
$builder->setStreamFactory(new StreamFactory()); // Set a PSR-18 StreamFactory (not needed if you are using zend-framework/zend-diactoros ^2
$builder->setHttpCodeDecider(new HttpCodeDecider()); // Set a class in charge of deciding the HTTP status code based on the response.
```

### Example

In this example, we will focus on getting a working version of GraphQLite using:

- [Zend Stratigility](https://docs.zendframework.com/zend-stratigility/) as a PSR-15 server
- `mouf/picotainer` (a micro-container) for the PSR-11 container
- `symfony/cache ` for the PSR-16 cache

The choice of the libraries is really up to you. You can adapt it based on your needs.

**composer.json**

```json
{
  "autoload": {
    "psr-4": {
      "App\\": "src/"
    }
  },
  "require": {
    "thecodingmachine/graphqlite": "^4",
    "zendframework/zend-diactoros": "^2",
    "zendframework/zend-stratigility": "^3",
    "zendframework/zend-httphandlerrunner": "^1.0",
    "mouf/picotainer": "^1.1",
    "symfony/cache": "^4.2"
  },
  "minimum-stability": "dev",
  "prefer-stable": true
}
```

**index.php**

```php
<?php

use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\ServerRequestFactory;
use Zend\HttpHandlerRunner\Emitter\SapiStreamEmitter;
use Zend\Stratigility\Middleware\ErrorResponseGenerator;
use Zend\Stratigility\MiddlewarePipe;
use Zend\Diactoros\Server;
use Zend\HttpHandlerRunner\RequestHandlerRunner;

require_once __DIR__ . '/vendor/autoload.php';

$container = require 'config/container.php';

$serverRequestFactory = [ServerRequestFactory::class, 'fromGlobals'];

$errorResponseGenerator = function (Throwable $e) {
    $generator = new ErrorResponseGenerator();
    return $generator($e, new ServerRequest(), new Response());
};

$runner = new RequestHandlerRunner(
    $container->get(MiddlewarePipe::class),
    new SapiStreamEmitter(),
    $serverRequestFactory,
    $errorResponseGenerator
);
$runner->run();
```

Here we are initializing a Zend `RequestHandler` (it receives requests) and we pass it to a Zend Stratigility `MiddlewarePipe`.
This `MiddlewarePipe` comes from the container declared in the `config/container.php` file:

**config/container.php**

```php
<?php

use GraphQL\Type\Schema;
use Mouf\Picotainer\Picotainer;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Simple\ApcuCache;
use TheCodingMachine\GraphQLite\Http\Psr15GraphQLMiddlewareBuilder;
use TheCodingMachine\GraphQLite\SchemaFactory;
use Zend\Stratigility\MiddlewarePipe;

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
        return new ApcuCache();
    },
    Schema::class => function(ContainerInterface $container) {
        // The magic happens here. We create a schema using GraphQLite SchemaFactory.
        $factory = new SchemaFactory($container->get(CacheInterface::class), $container);
        $factory->addControllerNamespace('App\\Controllers\\');
        $factory->addTypeNamespace('App\\');
        return $factory->createSchema();
    }
]);
```

Now, we need to add a first query and therefore create a controller.
The application will look into the `App\Controllers` namespace for GraphQLite controllers.

It assumes that the container has an entry whose name is the controller's fully qualified class name.


**src/Controllers/MyController.php**

```php
namespace App\Controllers;

use TheCodingMachine\GraphQLite\Annotations\Query;

class MyController
{
    /**
     * @Query
     */
    public function hello(string $name): string
    {
        return 'Hello '.$name;
    }
}
```

**config/container.php**

```php
use App\Controllers\MyController;

return new Picotainer([
    // ...
    
    // We declare the controller in the container.
    MyController::class => function() {
        return new MyController();
    },
]);
```

And we are done! You can now test your query using your favorite GraphQL client.

![](assets/query1.png)
