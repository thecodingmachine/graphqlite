---
id: version-3.0-type_mapping
title: Type mapping
sidebar_label: Type mapping
original_id: type_mapping
---

As explained in the [queries](queries.md) section, the job of GraphQLite is to create GraphQL types from PHP types.

## Scalar mapping

Scalar PHP types can be type-hinted to the corresponding GraphQL types:

* `string`
* `int`
* `bool`
* `float`

For instance:

```php
namespace App\Controller;

use TheCodingMachine\GraphQLite\Annotations\Query;

class MyController
{
    /**
     * @Query
     */
    public function hello(string $name): string
    {
        return 'Hello ' . $name;
    }
}
```

## Class mapping

When returning a PHP class in a query, you must annotate this class using `@Type` and `@Field` annotations:

```php
namespace App\Entities;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Type;

/**
 * @Type()
 */
class Product
{
    // ...

    /**
     * @Field()
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @Field()
     */
    public function getPrice(): ?float
    {
        return $this->price;
    }
}
```

## Array mapping

You can type-hint against arrays (or iterators) as long as you add a detailed `@return` statement in the PHPDoc.

```php
/**
 * @Query
 * @return User[] <=== we specify that the array is an array of User objects.
 */
public function users(int $limit, int $offset): array
{
    // Some code that returns an array of "users".
}
```

## ID mapping

GraphQL comes with a native `ID` type. PHP has no such type.

There are two ways with GraphQLite to handle such type.

### Force the outputType

```php
/**
 * @Field(outputType="ID")
 */
public function getId(): string
{
    // ...
}
```

Using the `outputType` attribute of the `@Field` annotation, you can force the output type to `ID`.

You can learn more about forcing output types in the [custom output types section](custom_output_types.md).

### ID class

```php
use TheCodingMachine\GraphQLite\Types\ID;

/**
 * @Field
 */
public function getId(): ID
{
    // ...
}
```

Note that you can also use the `ID` class as an input type:

```php
use TheCodingMachine\GraphQLite\Types\ID;

/**
 * @Mutation
 */
public function save(ID $id, string $name): Product
{
    // ...
}
```

## Date mapping

Out of the box, GraphQL does not have a `DateTime` type, but we took the liberty to add one, with sensible defaults.

When used as an output type, `DateTimeImmutable` or `DateTimeInterface` PHP classes are 
automatically mapped to this `DateTime` GraphQL type.

```php
/**
 * @Field
 */
public function getDate(): \DateTimeInterface
{
    return $this->date;
}
```

The `date` field will be of type `DateTime`. In the returned JSON response to a query, the date is formatted as a string
in the **ISO8601** format (aka ATOM format).

<div class="alert alert-error">
    PHP <code>DateTime</code> type is not supported.
</div>

## Union types

You can create a GraphQL union type *on the fly* using the pipe `|` operator in the PHPDoc:

```php
/**
 * @Query
 * @return Company|Contact <== can return a company OR a contact.
 */
public function companyOrContact(int $id)
{
    // Some code that returns a company or a contact.
}
```

