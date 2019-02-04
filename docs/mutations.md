---
id: mutations
title: Mutations
sidebar_label: Mutations
---

In GraphQLite, mutations are created [like queries](queries.md).

To create a mutation, you must annotate a method in a controller with the `@Mutation` annotation.

For instance:

```php
namespace App\Controller;

use TheCodingMachine\GraphQLite\Annotations\Mutation;

class ProductController
{
    /**
     * @Mutation
     */
    public function saveProduct(int $id, string $name, ?float $price = null): Product
    {
        // Some code that saves a product.
    }
}
```
