---
id: type_mapping
title: Type mapping
sidebar_label: Type mapping
---

The job of GraphQLite is to create GraphQL types from PHP types.

Internally, GraphQLite uses a "type mapper".

## Mapping a PHP class to a GraphQL type

The ["my first query"](my_first_query.md) documentation page 
already explains how to use the `@Type` annotation to map a PHP class to a GraphQL type. Please refer to this documentation
for class mapping

## Mapping of scalar types

Scalar PHP types can be type-hinted to the corresponding GraphQL types:

- string
- int
- bool
- float

## Mapping of arrays

You can type-hint against arrays (or iterators) as long as you add a detailed `@return` statement in the PHP-Doc:

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

## Mapping of ID type

GraphQL comes with a native "ID" type. PHP has no such type.

If you want to expose an "ID" type in your GraphQL model, you have 2 solutions:

### Solution 1: force the outputType

```php
/**
 * @Field(outputType="ID")
 */
public function getId(): string
{

}
```

Using the "outputType" attribute of the `@Field` annotation, you can force the output type to "ID".
You can learn more about [forcing output types in the "custom output types" documentation](custom_output_types.md).

### Solution 2: use the "ID" class

```php
use TheCodingMachine\GraphQLite\Types\ID;

/**
 * @Field
 */
public function getId(): ID
{

}
```

Note that you can also use the "ID" class as an input type:

```php
use TheCodingMachine\GraphQLite\Types\ID;

/**
 * @Mutation
 */
public function save(ID $id, string $name): Product
{

}
```

## Mapping of dates

Out of the box, GraphQL does not have a `DateTime` type, but we took the liberty to add one, with sensible defaults.

When used as an output type (i.e. in a "return type"), `DateTimeImmutable` or `DateTimeInterface` PHP classes are 
automatically mapped to this `DateTime` GraphQL type.

```php
/**
 * @Field
 */
public function getDate(): \DateTimeInterface
{

}
```

The "date" field will be of type "DateTime". In the returned JSON response to a query, the date is formatted as a string
in the ISO8601 format (aka ATOM format).

When used in an "input type" (i.e. in arguments of a method), the <code>DateTime</code> PHP class is not supported. 
Only the <code>DateTimeImmutable</code> PHP class is mapped. 

<div class="alert alert-success">This is ok:</div>

```php
/**
 * @Query
 * @return Product[]
 */
public function getProducts(\DateTimeImmutable $fromDate): array
{

}
```

<div class="alert alert-error">But <code>DateTime</code> input type is not supported:</div>

```php
/**
 * @Query
 * @return Product[]
 */
public function getProducts(\DateTime $fromDate): array // BAD
{

}
```

## Union types
--------------

You can create a GraphQL union type "on the fly", using the pipe (|) operator in the PHPDoc:

```php
/**
 * @Query
 * @return Company|Contact <== can return a company OR a contact
 */
public function companyOrContact(int $id)
{
    // Some code that returns a company or a contact.
}
```

