---
id: version-4.0-extend_type
title: Extending a type
sidebar_label: Extending a type
original_id: extend_type
---

Fields exposed in a GraphQL type do not need to be all part of the same class.

Use the `@ExtendType` annotation to add additional fields to a type that is already declared.

<div class="alert alert-info">
    Extending a type has nothing to do with type inheritance. 
    If you are looking for a way to expose a class and its children classes, have a look at 
    the <a href="inheritance-interfaces">Inheritance</a> section</a>
</div>

Let's assume you have a `Product` class. In order to get the name of a product, there is no `getName()` method in 
the product because the name needs to be translated in the correct language. You have a `TranslationService` to do that.

```php
namespace App\Entities;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Type;

/**
 * @Type()
 */
class Product
{
    // ...

    /**
     * @Field()
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @Field()
     */
    public function getPrice(): ?float
    {
        return $this->price;
    }
}
```

```php
// You need to use a service to get the name of the product in the correct language. 
$name = $translationService->getProductName($productId, $language);
```

Using `@ExtendType`, you can add an additional `name` field to your product:

```php
namespace App\Types;

use TheCodingMachine\GraphQLite\Annotations\ExtendType;
use TheCodingMachine\GraphQLite\Annotations\Field;
use App\Entities\Product;

/**
 * @ExtendType(class=Product::class)
 */
class ProductType
{
    private $translationService;
    
    public function __construct(TranslationServiceInterface $translationService)
    {
        $this->translationService = $translationService;
    }
    
    /**
     * @Field()
     */
    public function getName(Product $product, string $language): string
    {
        return $this->translationService->getProductName($product->getId(), $language);
    }
}
```

Let's break this sample:

```php
/**
 * @ExtendType(class=Product::class)
 */
```

With the `@ExtendType` annotation, we tell GraphQLite that we want to add fields in the GraphQL type mapped to
the `Product` PHP class.

```php
class ProductType
{
    private $translationService;
    
    public function __construct(TranslationServiceInterface $translationService)
    {
        $this->translationService = $translationService;
    }
    
    // ...
}
```


- The `ProductType` class must be in the types namespace. You configured this namespace when you installed GraphQLite.
- The `ProductType` class is actually a **service**. You can therefore inject dependencies in it (like the `$translationService` in this example)

<div class="alert alert-warning"><strong>Heads up!</strong> The <code>ProductType</code> class must exist in the container of your 
application and the container identifier MUST be the fully qualified class name.<br/><br/>
If you are using the Symfony bundle (or a framework with autowiring like Laravel), this 
is usually not an issue as the container will automatically create the controller entry if you do not explicitly 
declare it.</div> 

```php
/**
 * @Field()
 */
public function getName(Product $product, string $language): string
{
    return $this->translationService->getProductName($product->getId(), $language);
}
```

The `@Field` annotation is used to add the "name" field to the `Product` type.

Take a close look at the signature. The first parameter is the "resolved object" we are working on.
Any additional parameters are used as arguments.

Using the "[Type language](https://graphql.org/learn/schema/#type-language)" notation, we defined a type extension for
the GraphQL "Product" type:

```graphql
Extend type Product {
    name(language: !String): String!
}
```

<div class="alert alert-success">Type extension is a very powerful tool. Use it to add fields that needs to be 
computed from services not available in the entity.
</div>
