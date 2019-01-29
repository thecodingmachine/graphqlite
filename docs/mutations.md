---
id: mutations
title: Writing mutations
sidebar_label: Mutations
---

In GraphQLite, mutations are created [just like queries](my_first_query.md).

To create a mutation, you annotate a method in a controller with the `@Mutation` annotation.

Here is a sample of a "saveProduct" query:

```php
namespace App\Controllers;

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
