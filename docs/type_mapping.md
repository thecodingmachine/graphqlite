---
id: type_mapping
title: Type mapping
sidebar_label: Type mapping
---

The job of GraphQL-Controllers is to create GraphQL types from PHP types.

Internally, GraphQL-Controllers uses a "type mapper".

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

## Mapping of ID type

GraphQL comes with a native "ID" type. PHP has no such type.

TODO: develop a ID PHP class type? (for input types?)


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



TODO: ID

TODO: Union type

TODO: External type
TODO: Extend class
TODO: Sourcefield (other doc)

