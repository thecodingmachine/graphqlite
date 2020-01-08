---
id: version-4.0-laravel-package
title: Getting started with Laravel
sidebar_label: Laravel package
original_id: laravel-package
---

The GraphQLite-Laravel package is compatible with **Laravel 5.7+** and **Laravel 6.x**.

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
    'guard' => ['web'],
];
```

The debug parameters are detailed in the [documentation of the Webonyx GraphQL library](https://webonyx.github.io/graphql-php/error-handling/)
which is used internally by GraphQLite.

## Configuring CSRF protection

<div class="alert alert-warning">By default, the <code>/graphql</code> route is placed under <code>web</code> middleware group which requires a 
<a href="https://laravel.com/docs/6.x/csrf">CSRF token</a>.</div>

You have 3 options:

- Use the `api` middleware
- Disable CSRF for GraphQL routes
- or configure your GraphQL client to pass the `X-CSRF-TOKEN` with every GraphQL query

### Use the `api` middleware

If you plan to use graphql for server-to-server connection only, you should probably configure GraphQLite to use the 
`api` middleware instead of the `web` middleware:

**config/graphqlite.php**
```php
<?php
return [
    'middleware' => ['api'],
    'guard' => ['api'],
];
```

### Disable CSRF for the /graphql route 

If you plan to use graphql from web browsers and if you want to explicitly allow access from external applications 
(through CORS headers), you need to disable the CSRF token.

Simply add `graphql` to `$except` in `app/Http/Middleware/VerifyCsrfToken.php`.

### Configuring your GraphQL client

If you are planning to use `graphql` only from your website domain, then the safest way is to keep CSRF enabled and
configure your GraphQL JS client to pass the CSRF headers on any graphql request.

The way you do this depends on the Javascript GraphQL client you are using.

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

## Adding GraphQL DevTools

GraphQLite does not include additional GraphQL tooling, such as the GraphiQL editor.
To integrate a web UI to query your GraphQL endpoint with your Laravel installation, 
we recommend installing [GraphQL Playground](https://github.com/mll-lab/laravel-graphql-playground)

```console
$ composer require mll-lab/laravel-graphql-playground
```

By default, the playground will be available at `/graphql-playground`.

You can also use any external client with GraphQLite, make sure to point it to the URL defined in the config (`'/graphql'` by default).

## Troubleshooting HTTP 419 errors

If HTTP requests to GraphQL endpoint generate responses with the HTTP 419 status code, you have an issue with the configuration of your
CSRF token. Please check again [the paragraph dedicated to CSRF configuration](#configuring-csrf-protection).