[![Latest Stable Version](https://poser.pugx.org/thecodingmachine/graphql-controllers/v/stable)](https://packagist.org/packages/thecodingmachine/graphql-controllers)
[![Total Downloads](https://poser.pugx.org/thecodingmachine/graphql-controllers/downloads)](https://packagist.org/packages/thecodingmachine/graphql-controllers)
[![Latest Unstable Version](https://poser.pugx.org/thecodingmachine/graphql-controllers/v/unstable)](https://packagist.org/packages/thecodingmachine/graphql-controllers)
[![License](https://poser.pugx.org/thecodingmachine/graphql-controllers/license)](https://packagist.org/packages/thecodingmachine/graphql-controllers)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/thecodingmachine/graphql-controllers/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/thecodingmachine/graphql-controllers/?branch=master)
[![Build Status](https://travis-ci.org/thecodingmachine/graphql-controllers.svg?branch=master)](https://travis-ci.org/thecodingmachine/graphql-controllers)
[![Coverage Status](https://coveralls.io/repos/thecodingmachine/graphql-controllers/badge.svg?branch=master&service=github)](https://coveralls.io/github/thecodingmachine/graphql-controllers?branch=master)


GraphQL controllers
===================

A utility library on top of `Youshido/graphql` library.

This library allows you to write your GraphQL queries in simple to write controllers:

```php
use TheCodingMachine\GraphQL\Controllers\Annotations\Query;
use TheCodingMachine\GraphQL\Controllers\Annotations\Mutation;

class UserController
{
    /**
     * @Query
     * @return User[]
     */
    public function users(int $limit, int $offset): array
    {
        // Some code that returns an array of "users".
        // This completely replaces the "resolve" method.
    }

    /**
     * @Mutation
     * @return User
     */
    public function saveUser(UserInput $user): User
    {
        // Some code that saves a user.
        // This completely replaces the "resolve" method.
    }
}
```

Your methods can type-hint against:

- int
- string
- bool
- float
- DateTimeImmutable or DateTimeInterface
- an array
- any object (if you provide an hydrator for the object type)

There is an additional support for authentication and authorization:

```php
use TheCodingMachine\GraphQL\Controllers\Annotations\Query;
use TheCodingMachine\GraphQL\Controllers\Annotations\Mutation;

class UserController
{
    /**
     * @Query
     * @Logged
     * @Right("CAN_VIEW_USER_LIST")
     * @return User[]
     */
    public function users(int $limit, int $offset): array
    {
        //
    }
}
```

Type-hinting against arrays
---------------------------

You can type-hint against arrays as long as you document the PHP-Doc correctly:

```php
/**
 * @Query
 * @return User[] <=== we specify that the array is an array of User objects.
 */
public function users(int $limit, int $offset): array
{
    // Some code that returns an array of "users".
    // This completely replaces the "resolve" method.
}
```

Type-hinting against objects (automatic)
----------------------------------------

When you specify an object type-hint, graphql-controllers will delegate the object creation to an hydrator.
You must pass this hydrator in parameter when building the `ControllerQueryProvider`.

Type-hinting against objects (manually)
---------------------------------------

As an alternative, you can also manually specify the GraphqlType of your return type manually.

```php
/**
 * @Query(returnType=UserListType::class)
 */
public function users(int $limit, int $offset)
{
    // Whatever the return type of the method, it will be managed as a GraphQL UserListType
    // UserListType must implement Youshido's TypeInterface
}
```

You can also specify the name of an entry in the container that resolves to the GraphQL type to be used.

```php
/**
 * @Query(returnType="userListType")
 */
public function users(int $limit, int $offset)
{
    // The return type is fetched from the container. Expected name is "userListType"
}
```

Note: for container discovery to work, you must pass the container when constructing the `ControllerQueryProvider` object.

Usage
-----

```bash
$ composer require thecodingmachine/graphql-controllers
```

The package contains a http-interop compatible middleware: `TheCodingMachine\GraphQL\Controllers\GraphQLMiddleware`.
Put this middleware in your middleware pipe.

The middleware expects a GraphQL schema to be created. This package comes with a GraphQL schema compatible with Youshido
schemas that will automatically be filled from the GraphQL controllers you will write.

Controllers will be fetched from the container (it must be PSR-11 compliant).

Pseudo-code to initialize the middleware looks like this:

```php
$queryProvider = new AggregateControllerQueryProvider([
        "myController1", // These are the name of entries in the container to fetch the GraphQL controllers
        "myController2"
    ],
    $container, // The container containing the controllers (PSR-11 compliant),
    $annotationReader, // A Doctrine annotation reader
    $typeMapper, // Object used to map PHP classes to GraphQL types. 
    $hydrator, // Object used to create Objects from sent data (mostly for mutation)
    $authenticationService, // Object to manage authentication (the @Logged annotation)
    AuthorizationServiceInterface $authorizationService // Object to manage authorization (the @Right annotation)
    )
);
```  



Troubleshooting
---------------

### Error: Maximum function nesting level of '100' reached

Youshido's GraphQL library tends to use a very deep stack. This error does not necessarily mean your code is going into an infinite loop.
Simply try to increase the maximum allowed nesting level in your XDebug conf:

```
xdebug.max_nesting_level=500
```
