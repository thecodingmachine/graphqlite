---
id: error-handling
title: Error handling
sidebar_label: Error handling
---

In GraphQL, when an error occurs, the server must add an "error" entry in the response.

```json
{
  "errors": [
    {
      "message": "Name for character with ID 1002 could not be fetched.",
      "locations": [ { "line": 6, "column": 7 } ],
      "path": [ "hero", "heroFriends", 1, "name" ],
      "extensions": {
        "category": "Exception"
      }
    }
  ]
}
```

You can generate such errors with GraphQLite by throwing a `GraphQLException`.

```php
use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;

throw new GraphQLException("Exception message");
```

## HTTP response code

By default, when you throw a `GraphQLException`, the HTTP status code will be 500.

If your exception code is in the 4xx - 5xx range, the exception code will be used as an HTTP status code.

```php
// This exception will generate a HTTP 404 status code
throw new GraphQLException("Not found", 404);
```

<div class="alert alert-info">GraphQL allows to have several errors for one request. If you have several 
<code>GraphQLException</code> thrown for the same request, the HTTP status code used will be the highest one.</div>

## Customizing the category

By default, GraphQLite adds a "category" entry in the "extensions section". You can customize the category with the 
4th parameter of the constructor:

```php
throw new GraphQLException("Not found", 404, null, "NOT_FOUND");
```

will generate:

```json
{
  "errors": [
    {
      "message": "Not found",
      "extensions": {
        "category": "NOT_FOUND"
      }
    }
  ]
}
```

## Customizing the extensions section

You can customize the whole "extensions" section with the 5th parameter of the constructor:

```php
throw new GraphQLException("Field required", 400, null, "VALIDATION", ['field' => 'name']);
```

will generate:

```json
{
  "errors": [
    {
      "message": "Field required",
      "extensions": {
        "category": "VALIDATION",
        "field": "name"
      }
    }
  ]
}
```

## Writing your own exceptions

Rather that throwing the base `GraphQLException`, you should consider writing your own exception.

Any exception that implements interface `TheCodingMachine\GraphQLite\Exceptions\GraphQLExceptionInterface` will be displayed
in the GraphQL "errors" section.

```php
class ValidationException extends Exception implements GraphQLExceptionInterface
{
    /**
     * Returns true when exception message is safe to be displayed to a client.
     */
    public function isClientSafe(): bool
    {
        return true;
    }

    /**
     * Returns string describing a category of the error.
     *
     * Value "graphql" is reserved for errors produced by query parsing or validation, do not use it.
     */
    public function getCategory(): string
    {
        return 'VALIDATION';
    }

    /**
     * Returns the "extensions" object attached to the GraphQL error.
     *
     * @return array<string, mixed>
     */
    public function getExtensions(): array
    {
        return [];
    }
}
```

## Many errors for one exception

Sometimes, you need to display several errors in the response. But of course, at any given point in your code, you can
throw only one exception.

If you want to display several exceptions, you can bundle these exceptions in a `GraphQLAggregateException` that you can
throw.

```php
use TheCodingMachine\GraphQLite\Exceptions\GraphQLAggregateException;

/**
 * @Query
 */
public function createProduct(string $name, float $price): Product
{
    $exceptions = new GraphQLAggregateException();

    if ($name === '') {
        $exceptions->add(new GraphQLException('Name cannot be empty', 400, null, 'VALIDATION'));
    }
    if ($price <= 0) {
        $exceptions->add(new GraphQLException('Price must be positive', 400, null, 'VALIDATION'));
    }

    if ($exceptions->hasExceptions()) {
        throw $exceptions;
    }
}
```

## Webonyx exceptions

GraphQLite is based on the wonderful webonyx/GraphQL-PHP library. Therefore, the Webonyx exception mechanism can
also be used in GraphQLite. This means you can throw a `GraphQL\Error\Error` exception or any exception implementing
[`GraphQL\Error\ClientAware` interface](http://webonyx.github.io/graphql-php/error-handling/#errors-in-graphql)

Actually, the `TheCodingMachine\GraphQLite\Exceptions\GraphQLExceptionInterface` extends Webonyx's `ClientAware` interface.

## Behaviour of exceptions that do not implement ClientAware

If an exception that does not implement `ClientAware` is thrown, by default, GraphQLite will not catch it.

The exception will propagate to your framework error handler/middleware that is in charge of displaying the classical error page.

You can [change the underlying behaviour of Webonyx to catch any exception and turn them into GraphQL errors](http://webonyx.github.io/graphql-php/error-handling/#debugging-tools).
The way you adjust the error settings depends on the framework you are using ([Symfony](symfony-bundle.md), [Laravel](laravel-package.md)).

<div class="alert alert-info">To be clear: we strongly discourage changing this setting. We strongly believe that the
default "RETHROW_UNSAFE_EXCEPTIONS" setting of Webonyx is the only sane setting (only putting in "errors" section exceptions 
designed for GraphQL).</div>
