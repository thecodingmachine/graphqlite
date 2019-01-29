---
id: external_type_declaration
title: External type declaration
sidebar_label: External type declaration
---

The "[My first query](my_first_query.md)" documentation page explains how to declare a GraphQL type by putting a `@Type`
annotation on the class you want to expose:

```php
/**
 * @Type()
 */
class Product
{
    // ...
}
```

But in some cases, you cannot or do not want to put an annotation on a domain class.

For instance:

- The class you want to annotate is part of a third party library and you cannot modify it
- You are doing domain-drive design and don't want to clutter your domain object with annotations from the view layer
- ...

Hopefully, GraphQLite lets you declare a type without touching the targeted class.

Actually, you can use the `@Type` annotation on a service (just like the [@ExtendType annotation](extend_type.md)):

```php
use TheCodingMachine\GraphQLite\Annotations\Type;
use TheCodingMachine\GraphQLite\Annotations\Field;
use App\Entities\Product;

/**
 * @Type(class=Product::class)
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
    public function getId(Product $product): string
    {
        return $product->getId();
    }
}
```

Let's break this sample:

```php
/**
 * @Type(class=Product::class)
 */
```

With the "class" attribute of the `@Type` annotation, we tell GraphQLite that we want to create a GraphQL type 
mapped to the `Product` PHP class.

```php
// ...
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
public function getId(Product $product): string
{
    return $product->getId();
}
```

Just like in "classic" type declaration, the `@Field` annotation is used to declare fields in the GraphQL type.

But take a close look at the signature. The first parameter is the "resolved object" we are working on.
Any additional parameters are used as arguments.

## The @SourceField annotation

If your object has a lot of getters, you might end up in your type class mapping a lot of redundant code:

```php
/**
 * @Type(class=Product::class)
 */
class ProductType
{
    /**
     * @Field()
     */
    public function getName(Product $product): string
    {
        return $product->getName();
    }
    
    /**
     * @Field()
     */
    public function getPrice(Product $product): ?float
    {
        return $product->getPrice();        
    }
    
    // ...
}
```

This is a lot of boilerplate code.

GraphQLite provides a shortcut for this:

```php
use TheCodingMachine\GraphQLite\Annotations\Type;
use TheCodingMachine\GraphQLite\Annotations\SourceField;
use App\Entities\Product;

/**
 * @Type(class=Product::class)
 * @SourceField(name="name")
 * @SourceField(name="price")
 */
class ProductType
{
}
```

By putting the `@SourceField` in the class docblock, you let GraphQLite know that the type exposes the
`getName` method of the underlying `Product` object (GraphQLite will look for methods named `name()`, `getName()` and `isName()`).

### Managing rights

You can also check for logged users or users with a specific right using the `logged` and `right` properties of the annotation:

```php
use TheCodingMachine\GraphQLite\Annotations\Type;
use TheCodingMachine\GraphQLite\Annotations\SourceField;
use TheCodingMachine\GraphQLite\Annotations\Right;
use App\Entities\Product;

/**
 * @Type(class=Product::class)
 * @SourceField(name="name")
 * @SourceField(name="price", logged=true, right=@Right(name="CAN_ACCESS_Price"))
 */
class ProductType extends AbstractAnnotatedObjectType
{
}
```

Just like the `@Logged` and `@Right` annotations for regular fields, you can define a default value to use
in case the user has insufficient permissions:

```php
/**
 * @SourceField(name="status", logged=true, right=@Right(name="CAN_ACCESS_STATUS"), failWith=null)
 */
```

The `failWith` value will be returned in case of insufficient permissions. Note that if you do not put the
`failWith` attribute and if a user has insufficient permissions, then the field will not appear at all in the schema.
Querying this field will therefore result in an error. 

## Declaring fields dynamically (without annotations)

In some very particular cases, you might not know exactly the list of `@SourceField` annotations at development time.
If you need to decide the list of `@SourceField` at runtime, you can implement the `FromSourceFieldsInterface`:

```php
use TheCodingMachine\GraphQLite\FromSourceFieldsInterface;

/**
 * @Type(class=Product::class)
 */
class ProductType implements FromSourceFieldsInterface
{
    /**
     * Dynamically returns the array of source fields to be fetched from the original object.
     *
     * @return SourceFieldInterface[]
     */
    public function getSourceFields(): array
    {
        // You may want to enable fields conditionally based on feature flags...
        if (ENABLE_STATUS_GLOBALLY) {
            return [
                new SourceField(['name'=>'status', 'logged'=>true]),
            ];        
        } else {
            return [];
        }
    }
}
```
