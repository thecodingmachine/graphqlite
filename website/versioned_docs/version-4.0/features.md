---
id: version-4.0-features
title: GraphQLite
sidebar_label: GraphQLite
original_id: features
---

<p align="center">
    <img src="https://graphqlite.thecodingmachine.io/img/logo.svg" alt="GraphQLite logo" width="250" height="250" />
</p>


A PHP library that allows you to write your GraphQL queries in simple-to-write controllers.

## Features

* Create a complete GraphQL API by simply annotating your PHP classes
* Framework agnostic, but Symfony, Laravel and PSR-15 bindings available!
* Comes with batteries included: queries, mutations, mapping of arrays / iterators, file uploads, security, validation, extendable types and more!

## Basic example

First, declare a query in your controller:

```php
class ProductController
{
    /**
     * @Query()
     */
    public function product(string $id): Product
    {
        // Some code that looks for a product and returns it.
    }
}
```

Then, annotate the `Product` class to declare what fields are exposed to the GraphQL API:

```php
/**
 * @Type()
 */
class Product
{
    /**
     * @Field()
     */
    public function getName(): string
    {
        return $this->name;
    }
    // ...
}
```

That's it, you're good to go! Query and enjoy!

```grapql
{
  product(id: 42) {
    name
  }
}
```