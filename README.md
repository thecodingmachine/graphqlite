
<p align="center">
    <img src="https://graphqlite.thecodingmachine.io/img/logo.svg" alt="GraphQLite logo" width="250" height="250" />
</p>
<h3 align="center">GraphQLite</h3>
<p align="center">GraphQL in PHP made easy.</p>
<p align="center"><a href="https://graphqlite.thecodingmachine.io">Documentation</a> &#183; <a href="/.github/CONTRIBUTING.md">Contributing</a></p>

<p align="center">
    <a href="https://packagist.org/packages/thecodingmachine/graphqlite" title="Latest Stable Version">
        <img src="https://poser.pugx.org/thecodingmachine/graphqlite/v/stable" alt="Latest Stable Version" />
    </a>
    <a href="https://packagist.org/packages/thecodingmachine/graphqlite" title="Total Downloads">
        <img src="https://poser.pugx.org/thecodingmachine/graphqlite/downloads" alt="Total Downloads" />
    </a>
    <a href="https://packagist.org/packages/thecodingmachine/graphqlite" title="License">
        <img src="https://poser.pugx.org/thecodingmachine/graphqlite/license" alt="License" />
    </a>
    <a href="https://github.com/thecodingmachine/graphqlite/actions" title="Continuous Integration">
        <img src="https://github.com/thecodingmachine/graphqlite/workflows/Continuous%20Integration/badge.svg" alt="Continuous Integration" />
    </a>
    <a href="https://codecov.io/gh/thecodingmachine/graphqlite" title="Code Coverage">
        <img src="https://codecov.io/gh/thecodingmachine/graphqlite/branch/master/graph/badge.svg" alt="Code Coverage" />
    </a>
</p>

---

A GraphQL library for PHP that allows you to use attributes (or annotations) to define your schema and write your queries and mutations using simple-to-write controllers.

## Features

* Create a complete GraphQL API by simply annotating your PHP classes
* Framework agnostic, but with [Symfony](https://github.com/thecodingmachine/graphqlite-bundle) and [Laravel](https://github.com/thecodingmachine/graphqlite-laravel) integrations available!
* Comes with batteries included :battery:: queries, mutations, mapping of arrays/iterators, file uploads, extendable types and more!

## Basic example

First, declare a query in your controller:

```php
class ProductController
{
    #[Mutation]
    public function updateProduct(Product $product): Product
    {
        // Some code that gets and updates a Product
        return $product;
    }
}
```

Then, annotate the `Product` class to declare what fields are exposed to the GraphQL API:

```php
#[Type]
#[Input(update: true)]
class Product
{
    #[Field]
    public function getName(): string
    {
        return $this->name;
    }
    
    #[Field]
    public function setName(string $name): void
    {
        $this->name = $name;
    }
    
    // ...
}
```

That's it, you're good to go :tada: mutate away!

```graphql
{
  updateProduct(product: {
    name: 'John Doe'
  }) {
    name
  }
}
```

Want to learn more? Head to the [documentation](https://graphqlite.thecodingmachine.io/)!
