
<p align="center">
    <img src="https://graphqlite.thecodingmachine.io/img/logo.svg" alt="GraphQLite logo" width="250" height="250" />
</p>
<h3 align="center">GraphQLite</h3>
<p align="center">GraphQL in PHP made easy.</p>
<p align="center"><a href="https://graphqlite.thecodingmachine.io">Documentation</a> &#183; <a href="/.github/CONTRIBUTING.md">Contributing</a></p>

---

A library that allows you to write your GraphQL queries in simple-to-write controllers.

## Features

* Create a complete GraphQL API by simply annotating your PHP classes
* Framework agnostic, but Symfony bundle available!
* Comes with batteries included :battery:: queries, mutations, mapping of arrays / iterators, file uploads, extendable types and more!

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

Want to learn more? Head to the [documentation](https://graphqlite.thecodingmachine.io/)!

## Badges

[![Latest Stable Version](https://poser.pugx.org/thecodingmachine/graphqlite/v/stable)](https://packagist.org/packages/thecodingmachine/graphqlite)
[![Total Downloads](https://poser.pugx.org/thecodingmachine/graphqlite/downloads)](https://packagist.org/packages/thecodingmachine/graphqlite)
[![Latest Unstable Version](https://poser.pugx.org/thecodingmachine/graphqlite/v/unstable)](https://packagist.org/packages/thecodingmachine/graphqlite)
[![License](https://poser.pugx.org/thecodingmachine/graphqlite/license)](https://packagist.org/packages/thecodingmachine/graphqlite)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/thecodingmachine/graphqlite/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/thecodingmachine/graphqlite/?branch=master)
[![Build Status](https://travis-ci.org/thecodingmachine/graphqlite.svg?branch=master)](https://travis-ci.org/thecodingmachine/graphqlite)
[![Coverage Status](https://coveralls.io/repos/thecodingmachine/graphqlite/badge.svg?branch=master&service=github)](https://coveralls.io/github/thecodingmachine/graphqlite?branch=master)
