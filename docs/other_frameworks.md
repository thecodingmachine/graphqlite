---
id: other-frameworks
title: Getting started with any framework
sidebar_label: Other frameworks / No framework
---

If you are using Symfony 4+, checkout the [Symfony bundle](symfony-bundle.md).

GraphQL-Controllers requires:

- A PSR-11 compatible container
- A PSR-16 cache

Additionally, you will need a way to route the HTTP requests to the underlying GraphQL library.
thecodingmachine/graphql-controllers uses internally [webonyx/graphql-php](http://webonyx.github.io/graphql-php/).
This library plays well with PSR-7 requests and there is a PSR-15 middleware available.

## Generating a GraphQL schema.

GraphQL-Controllers purpose is to generate a [Webonyx GraphQL `Schema`](https://webonyx.github.io/graphql-php/type-system/schema/).

The easiest way to create such a schema is to use the `SchemaFactory` class.

```php
use TheCodingMachine\GraphQL\Controllers\SchemaFactory;

// $cache is a PSR-16 compatible cache
// $container is a PSR-11 compatible container
$factory = new SchemaFactory($cache, $container);
$factory->addControllerNamespace('App\\Controllers\\')
        ->addTypeNamespace('App\\');

$schema = $factory->createSchema();
```

You can now use the schema to resolve GraphQL queries (using [Webonyx's GraphQL facade](https://webonyx.github.io/graphql-php/getting-started/#hello-world) 
or the [StandardServer class](https://webonyx.github.io/graphql-php/executing-queries/#using-server)).

## Absolutely minimal sample

The smallest working example using no framework is:

```php
<?php
use GraphQL\GraphQL;
use GraphQL\Type\Schema;
use TheCodingMachine\GraphQL\Controllers\SchemaFactory;

// $cache is a PSR-16 compatible cache
// $container is a PSR-11 compatible container
$factory = new SchemaFactory($cache, $container);
$factory->addControllerNamespace('App\\Controllers\\')
        ->addTypeNamespace('App\\');

$schema = $factory->createSchema();

$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);
$query = $input['query'];
$variableValues = isset($input['variables']) ? $input['variables'] : null;

$result = GraphQL::executeQuery($schema, $query, null, null, $variableValues);
$output = $result->toArray();

header('Content-Type: application/json');
echo json_encode($output);
```

## Factory options

The `SchemaFactory` class comes with a number of methods that you can use to customize your GraphQL-Controllers settings.

```php
// Configure an authentication service (to resolve the @Logged annotations)
$factory->setAuthenticationService(new VoidAuthenticationService());
// Configure an authorization service (to resolve the @Right annotations)
$factory->setAuthorizationService(new VoidAuthorizationService())
// Change the naming convention of GraphQL types globally
$factory->setNamingStrategy(new NamingStrategy())
// Add a custom type mapper
$factory->addTypeMapper($typeMapper)
// Add custom options to the Webonyx underlying Schema
$factory->setSchemaConfig($schemaConfig);
```


## A more advanced sample

In this sample, we will focus on getting a working version of GraphQL-Controllers using:

- Zend Stratigility for the PSR-7 server
- "phps-cans/psr7-middleware-graphql" to route PSR-7 requests to the GraphQL engine
- mouf/picotainer (a micro-container) for the PSR-11 container
- symfony/cache for the PSR-16 cache

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
    "thecodingmachine/graphql-controllers": "^3",
    "phps-cans/psr7-middleware-graphql": "^0.2",
    "zendframework/zend-diactoros": "^2",
    "zendframework/zend-stratigility": "^3",
    "mouf/picotainer": "^1.1",
    "symfony/cache": "^4.2"
  },
  "minimum-stability": "dev",
  "prefer-stable": true
}
```

We now need to initialize Stratigility:

TODO




```bash
$ composer require thecodingmachine/graphql-controllers
```

The package contains a PSR-15 compatible middleware: `TheCodingMachine\GraphQL\Controllers\GraphQLMiddleware`.
Put this middleware in your middleware pipe.

The middleware expects a GraphQL schema to be created. This package comes with a GraphQL schema compatible with Webonix
schemas that will automatically be filled from the GraphQL controllers you will write.

Controllers will be fetched from the container (it must be PSR-11 compliant).

Pseudo-code to initialize the middleware looks like this:

```php
$registry = new Registry(
  $container, // The container containing the controllers (PSR-11 compliant),
  $authorizationService // Object to manage authorization (the @Right annotation)  
  $authenticationService, // Object to manage authentication (the @Logged annotation)
  $annotationReader, // A Doctrine annotation reader
  $typeMapper, // Object used to map PHP classes to GraphQL types.
  $hydrator, // Object used to create Objects from sent data (mostly for mutation)
);

$queryProvider = new AggregateControllerQueryProvider([
        "myController1", // These are the name of entries in the container to fetch the GraphQL controllers
        "myController2"
    ],
    $registry
  );
```

Alternatively, you can use auto-discovery of the controllers if you put them in a common namespace:

```php
$queryProvider = new GlobControllerQueryProvider('App\\GraphQL\\Controllers', $this->getRegistry(), $container, $cache, $cacheTtl);
```

The application will look into the 'App\\GraphQL\\Controllers' namespace for GraphQL controllers. It assumes that the 
container contains an entry whose name is the fully qualified class name of the container.
Note: $cache is a PSR-16 compatible cache.