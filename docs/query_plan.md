---
id: query-plan
title: Query plan
sidebar_label: Query plan
---

## The problem

GraphQL naive implementations often suffer from the "N+1" problem.

Let's have a look at the following query:

```graphql
{
    products {
        name
        manufacturer {
            name
        }
    }
}
```

A naive implementation will do this:

- 1 query to fetch the list of products
- 1 query per product to fetch the manufacturer

Assuming we have "N" products, we will make "N+1" queries.

There are several ways to fix this problem. Assuming you are using a relational database, one solution is to try to look 
ahead and perform only one query with a JOIN between "products" and "manufacturers".

But how do I know if I should make the JOIN between "products" and "manufacturers" or not? I need to know ahead
of time.

With GraphQLite, you can answer this question by tapping into the `ResolveInfo` object.

## Fetching the query plan

<small>Available in GraphQLite 4.0+</small>

```php
use GraphQL\Type\Definition\ResolveInfo;

class ProductsController
{
    /**
     * @Query
     * @return Product[]
     */
    public function products(ResolveInfo $info): array
    {
        if (isset($info->getFieldSelection()['manufacturer']) {
            // Let's perform a request with a JOIN on manufacturer
        } else {
            // Let's perform a request without a JOIN on manufacturer
        }
        // ...
    }
}
```

`ResolveInfo` is a class provided by Webonyx/GraphQL-PHP (the low-level GraphQL library used by GraphQLite).
It contains info about the query and what fields are requested. Using `ResolveInfo::getFieldSelection` you can analyze the query 
and decide whether you should perform additional "JOINS" in your query or not.

<div class="alert alert-info">As of the writing of this documentation, the <code>ResolveInfo</code> class is useful but somewhat limited.
The <a href="https://github.com/webonyx/graphql-php/pull/436">next version of Webonyx/GraphQL-PHP will add a "query plan"</a>
that allows a deeper analysis of the query.</div>
