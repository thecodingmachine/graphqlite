---
id: version-3.0-laravel-package
title: Getting started with Laravel
sidebar_label: Laravel package
original_id: laravel-package
---

The GraphQLite-Laravel package is compatible with **Laravel 5.x**.

## Installation

Open a terminal in your current project directory and run:

```console
$ composer require thecodingmachine/graphqlite-laravel
```

If you want to publish the configuration (in order to edit it), run:

```console
$ php artisan vendor:publish --provider=TheCodingMachine\GraphQLite\Laravel\Providers\GraphQLiteServiceProvider
```

You can then configure the library by editing `config/graphqlite.php`.

**config/graphqlite.php**
```php
<?php

use GraphQL\Error\Debug;

return [
    /*
     |--------------------------------------------------------------------------
     | GraphQLite Configuration
     |--------------------------------------------------------------------------
     |
     | Use this configuration to customize the namespace of the controllers and
     | types.
     | These namespaces must be autoloadable from Composer.
     | GraphQLite will find the path of the files based on composer.json settings.
     |
     | You can put a single namespace, or an array of namespaces.
     |
     */
    'controllers' => 'App\\Http\\Controllers',
    'types' => 'App\\',
    'debug' => Debug::RETHROW_UNSAFE_EXCEPTIONS,
    'uri' => '/graphql'
];
```

The debug parameters are detailed in the [documentation of the Webonyx GraphQL library](https://webonyx.github.io/graphql-php/error-handling/)
which is used internally by GraphQLite.


## Adding GraphQL DevTools

GraphQLite does not include additional GraphQL tooling, such as the GraphiQL editor.
To integrate a web UI to query your GraphQL endpoint with your Laravel installation, 
we recommend installing GraphQL Playground

```console
$ composer require mll-lab/laravel-graphql-playground
```

You can also use any external client with GraphQLite, make sure to point it to the URL defined in the config (`'/graphql'` by default).
