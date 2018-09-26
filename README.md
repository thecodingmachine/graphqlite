[![Latest Stable Version](https://poser.pugx.org/thecodingmachine/graphql-controllers/v/stable)](https://packagist.org/packages/thecodingmachine/graphql-controllers)
[![Total Downloads](https://poser.pugx.org/thecodingmachine/graphql-controllers/downloads)](https://packagist.org/packages/thecodingmachine/graphql-controllers)
[![Latest Unstable Version](https://poser.pugx.org/thecodingmachine/graphql-controllers/v/unstable)](https://packagist.org/packages/thecodingmachine/graphql-controllers)
[![License](https://poser.pugx.org/thecodingmachine/graphql-controllers/license)](https://packagist.org/packages/thecodingmachine/graphql-controllers)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/thecodingmachine/graphql-controllers/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/thecodingmachine/graphql-controllers/?branch=master)
[![Build Status](https://travis-ci.org/thecodingmachine/graphql-controllers.svg?branch=master)](https://travis-ci.org/thecodingmachine/graphql-controllers)
[![Coverage Status](https://coveralls.io/repos/thecodingmachine/graphql-controllers/badge.svg?branch=master&service=github)](https://coveralls.io/github/thecodingmachine/graphql-controllers?branch=master)


GraphQL controllers
===================

A utility library on top of `webonyx/graphql-php` library.

Note: v1 and v2 of this library was built on top of `youshido/graphql`.

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
    // UserListType must extend Webonix's Type
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

Overriding the query name
-------------------------

By default, the name of the query is the name of the method.
You can override the name of the query by passing the "name" attribute to the @Query annotation:

```php
/**
 * @Query(name="my_query_name")
 */
public function someMethodName()
{
    // The GraphQL query name will be "my_query_name",  not "someMethodName"
}
```

You can of course do the same thing with the "@Mutation" annotation.


Usage
-----

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


Defining object types
=====================

When you use webonyx/graphql-php, you will typically extend the `AbstractObjectType` class to declare your GraphQL types.

Typical code looks like this:

```php
class PostType extends AbstractObjectType
{

    public function build($config)
    {
        // you can define fields in a single addFields call instead of chaining multiple addField()
        $config->addFields([
            'title'      => [
                'type' => new StringType(),
                'args'              => [
                    'truncate' => new BooleanType()
                ],
                'resolve'           => function (Post $source, $args) {
                    return (!empty($args['truncate'])) ? explode(' ', $source->getTitle())[0] . '...' : $source->getTitle();
                }
            ]
        ]);
    }
}
```

In graphql-controllers, you can instead define a simple class with annotations:

```php
/**
 * @Type(class=Post::class)
 */
class PostType extends AbstractAnnotatedObjectType
{
    /**
     * @Field()
     */
    public function customField(Post $source, bool $truncate = false): string
    {
        return (!empty($args['truncate'])) ? explode(' ', $source->getTitle())[0] . '...' : $source->getTitle();
    }
}
```

TODO: continue


Using @Field annotation in object types
=======================================

When you use webonyx/graphql-php, you will typically extend the `AbstractObjectType` class to declare your GraphQL types.

Typical code looks like this:

```php
class PostType extends AbstractObjectType
{

    public function build($config)
    {
        // you can define fields in a single addFields call instead of chaining multiple addField()
        $config->addFields([
            'title'      => [
                'type' => new StringType(),
                'args'              => [
                    'truncate' => new BooleanType()
                ],
                'resolve'           => function (Post $source, $args) {
                    return (!empty($args['truncate'])) ? explode(' ', $source->getTitle())[0] . '...' : $source->getTitle();
                }
            ]
        ]);
    }
}
```

You can replace the whole `build` method with methods with the @Field annotation:

```php
class PostType extends AbstractAnnotatedObjectType
{
    /**
     * @Field()
     */
    public function customField(Post $source, bool $truncate = false): string
    {
        return (!empty($args['truncate'])) ? explode(' ', $source->getTitle())[0] . '...' : $source->getTitle();
    }
}
```

You simply have to:

- extend the `AbstractAnnotatedObjectType` class
- add the @Field annotation
- when constructing the object, you must pass the $registry object as the first argument:
  ```php
  $postType = new PostType($registry);
  ```

Please note that the first argument of the method is the object we are calling the field on. The remaining arguments are converted to GraphQL arguments of the field.

Just like the @Query and @Mutation annotations, the @Field annotation can be passed an optional "name" and "returnType" attribute:

```php
class PostType extends AbstractAnnotatedObjectType
{
    /**
     * @Field(name="customField", returnType="myCustomField")
     */
    public function getCustomField(Post $source, bool $truncate = false): CustomField
    {
        // ...
    }
}
```

The @SourceField annotation
----------------------------

If your object has a lot of getters, you might end up in your type class mapping a lot of redundant code:

```php
class PostType extends AbstractAnnotatedObjectType
{
    /**
     * @Field(name="name")
     */
    public function getName(Post $source): string
    {
        return $source->getName();
    }
}
```

GraphQL-controllers provides a shortcut for this:

```php
/**
 * @Type(class=Post::class)
 * @SourceField(name="name")
 */
class PostType extends AbstractAnnotatedObjectType
{
}
```

By putting the `@SourceField` in the class docblock, you let GraphQL-controllers now that the type should expose the
`getName` method of the underlying `Post` object.

For the `@SourceField` annotation to work, you need to add a `@Type` annotation that will let the GraphQL-controllers
library now the underlying type.

You can also check for logged users or users with a specific right using the `logged` and `right` properties of the annotation:

```php
/**
 * @Type(class=Post::class)
 * @SourceField(name="name")
 * @SourceField(name="status", logged=true, right=@Right(name="CAN_ACCESS_STATUS"))
 */
class PostType extends AbstractAnnotatedObjectType
{
}
```



Troubleshooting
---------------

### Error: Maximum function nesting level of '100' reached

Webonix's GraphQL library tends to use a very deep stack. This error does not necessarily mean your code is going into an infinite loop.
Simply try to increase the maximum allowed nesting level in your XDebug conf:

```
xdebug.max_nesting_level=500
```
