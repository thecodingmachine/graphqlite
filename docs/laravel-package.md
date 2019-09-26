---
id: laravel-package
title: Getting started with Laravel
sidebar_label: Laravel package
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
    'uri' => env('GRAPHQLITE_URI', '/graphql'),
    'middleware' => ['web'],
];
```

The debug parameters are detailed in the [documentation of the Webonyx GraphQL library](https://webonyx.github.io/graphql-php/error-handling/)
which is used internally by GraphQLite.

## Passing the XSRF token

<div class="alert alert-warning">By default `/graphql` route is placed under `web` middleware group which requires a CSRF token.</div>

You have 2 options:

- Configure your GraphQL client to pass the `X-CSRF-TOKEN` with every GraphQL query
- or disable CSRF for GraphQL routes (not recommended)

### Configuring your GraphQL client

This step depends on the Javascript GraphQL client you are using.

Assuming you are using [Apollo](https://www.apollographql.com/docs/link/links/http/), you need to be sure that Apollo passes the token
back to Laravel on every request.

**Sample Apollo client setup with CSRF support**
```js
import { ApolloClient, ApolloLink, InMemoryCache, HttpLink } from 'apollo-boost';

const httpLink = new HttpLink({ uri: 'https://api.example.com/graphql' });

const authLink = new ApolloLink((operation, forward) => {
  // Retrieve the authorization token from local storage.
  const token = localStorage.getItem('auth_token');
  
  // Get the XSRF-TOKEN that is set by Laravel on each request
  var cookieValue = document.cookie.replace(/(?:(?:^|.*;\s*)XSRF-TOKEN\s*\=\s*([^;]*).*$)|^.*$/, "$1");

  // Use the setContext method to set the X-CSRF-TOKEN header back.
  operation.setContext({
    headers: {
      'X-CSRF-TOKEN': cookieValue
    }
  });

  // Call the next link in the middleware chain.
  return forward(operation);
});

const client = new ApolloClient({
  link: authLink.concat(httpLink), // Chain it with the HttpLink
  cache: new InMemoryCache()
});
```

### Alternative: disable CSRF for the /graphql route 

Alternatively, you can add `graphql` to `$except` in `app/Http/Middleware/VerifyCsrfToken.php`.

## Adding GraphQL DevTools

GraphQLite does not include additional GraphQL tooling, such as the GraphiQL editor.
To integrate a web UI to query your GraphQL endpoint with your Laravel installation, 
we recommend installing [GraphQL Playground](https://github.com/mll-lab/laravel-graphql-playground)

```console
$ composer require mll-lab/laravel-graphql-playground
```

By default, the playground will be available at `/graphql-playground`.

You can also use any external client with GraphQLite, make sure to point it to the URL defined in the config (`'/graphql'` by default).
