---
id: pagination
title: Paginating large result sets
sidebar_label: Pagination
---

It is quite common to have to paginate over large result sets.

GraphQLite offers a simple way to do that using [Porpaginas](https://github.com/beberlei/porpaginas).

Porpaginas is a set of PHP interfaces that can be implemented by result iterators. It comes with a native support for
PHP arrays, Doctrine and [TDBM](https://thecodingmachine.github.io/tdbm/doc/limit_offset_resultset.html).

<div class="alert alert-warning">If you are a Laravel user, Eloquent does not come with a Porpaginas
iterator. However, the GraphQLite Laravel bundle <a href="laravel-package-advanced.md">comes with its own pagination system</a>.</div>

## Usage

In your query, simply return a class that implements `Porpaginas\Result`:

```php
class MyController
{
    /**
     * @Query
     * @return Product[]
     */
    public function products(): Porpaginas\Result
    {
        // Some code that returns a list of products

        // If you are using Doctrine, something like:
        return new Porpaginas\Doctrine\ORM\ORMQueryResult($doctrineQuery);
    }
}
```

Notice that:

- the method return type MUST BE `Porpaginas\Result` or a class implementing `Porpaginas\Result`
- you MUST add a `@return` statement to help GraphQLite find the type of the list

Once this is done, you can paginate directly from your GraphQL query:

```
products {
    items(limit: 10, offset: 20) {
        id
        name
    }
    count
}
```

Results are wrapped into an item field. You can use the "limit" and "offset" parameters to apply pagination automatically.

The "count" field returns the **total count** of items.
