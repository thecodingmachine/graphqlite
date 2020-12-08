---
id: version-4.1-mutations
title: Mutations
sidebar_label: Mutations
original_id: mutations
---

In GraphQLite, mutations are created [like queries](queries.md).

To create a mutation, you must annotate a method in a controller with the `@Mutation` annotation.

For instance:

<!--DOCUSAURUS_CODE_TABS-->
<!--PHP 8+-->
```php
namespace App\Controller;

use TheCodingMachine\GraphQLite\Annotations\Mutation;

class ProductController
{
    #[Mutation]
    public function saveProduct(int $id, string $name, ?float $price = null): Product
    {
        // Some code that saves a product.
    }
}
```
<!--PHP 7+-->
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
<!--END_DOCUSAURUS_CODE_TABS-->
