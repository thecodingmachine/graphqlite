---
id: migrating
title: Release notes
sidebar_label: Release notes
original_id: migrating
---

## First stable release of GraphQLite

GraphQLite is PHP library that allows you to write your GraphQL queries in simple-to-write controllers.

- Create a complete GraphQL API by simply annotating your PHP classes
- Framework agnostic, but Symfony and Laravel bindings available!
- Comes with batteries included: queries, mutations, mapping of arrays / iterators, file uploads, extendable types and more!

After several months of work, we are very happy to announce the availability of GraphQLite v3.0.

If you are wondering where are v1 and v2... yeah... GraphQLite is a fork of "thecodingmachine/graphql-controllers" that already had a v1 and a v2. But so much has changed that it deserved a new name!

[Check out the documentation](https://graphqlite.thecodingmachine.io)

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

That's it, you're good to go :tada:! Query and enjoy!

```graphql
{
  product(id: 42) {
    name
  }
}
```
