---
id: version-3.0-universal_service_providers
title: Getting started with a framework compatible with container-interop/service-provider
sidebar_label: Universal service providers
original_id: universal_service_providers
---

[container-interop/service-provider](https://github.com/container-interop/service-provider/) is an experimental project
aiming to bring interoperability between framework module systems.

If your framework is compatible with [container-interop/service-provider](https://github.com/container-interop/service-provider/),
GraphQLite comes with a service provider that you can leverage.

## Installation

Open a terminal in your current project directory and run:

```console
$ composer require thecodingmachine/graphqlite-universal-service-provider
```

## Requirements

In order to bootstrap GraphQLite, you will need:

- A PSR-16 cache

Additionally, you will have to route the HTTP requests to the underlying GraphQL library.

GraphQLite relies on the [webonyx/graphql-php](http://webonyx.github.io/graphql-php/) library internally.
This library plays well with PSR-7 requests and there is a [PSR-15 middleware available](https://github.com/phps-cans/psr7-middleware-graphql).

## Integration

Webonyx/graphql-php library requires a [Schema](https://webonyx.github.io/graphql-php/type-system/schema/) in order to resolve
GraphQL queries. The service provider provides this `Schema` class.

[Checkout the the service-provider documentation](https://github.com/thecodingmachine/graphqlite-universal-service-provider)

## Sample usage

**composer.json**
```
{
  "require": {
    "mnapoli/simplex": "^0.5",
    "thecodingmachine/graphqlite-universal-service-provider": "^3",
    "thecodingmachine/symfony-cache-universal-module": "^1"
  },
  "minimum-stability": "dev",
  "prefer-stable": true
}
```

**index.php**
```php
use Simplex\Container;
use TheCodingMachine\GraphQLite\Schema;
use TheCodingMachine\SymfonyCacheServiceProvider;
use TheCodingMachine\DoctrineAnnotationsServiceProvider;
use TheCodingMachine\GraphQLiteServiceProvider;

$container = new Container([
    new SymfonyCacheServiceProvider(),
    new DoctrineAnnotationsServiceProvider,
    new GraphQLiteServiceProvider()]);
$container->set('graphqlite.namespace.types', ['App\\Types']);
$container->set('graphqlite.namespace.controllers', ['App\\Controllers']);

$schema = $container->get(Schema::class);
```
