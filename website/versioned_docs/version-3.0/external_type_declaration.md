---
id: version-3.0-external_type_declaration
title: External type declaration
sidebar_label: External type declaration
original_id: external_type_declaration
---

In some cases, you cannot or do not want to put an annotation on a domain class.

For instance:

* The class you want to annotate is part of a third party library and you cannot modify it
* You are doing domain-driven design and don't want to clutter your domain object with annotations from the view layer
* etc.

## `@Type` annotation with the `class` attribute

GraphQLite allows you to use a *proxy* class thanks to the `@Type` annotation with the `class` attribute:

```php
namespace App\Types;

use TheCodingMachine\GraphQLite\Annotations\Type;
use TheCodingMachine\GraphQLite\Annotations\Field;
use App\Entities\Product;

/**
 * @Type(class=Product::class)
 */
class ProductType
{    
    /**
     * @Field()
     */
    public function getId(Product $product): string
    {
        return $product->getId();
    }
}
```

The `ProductType` class must be in the *types* namespace. You configured this namespace when you installed GraphQLite.

The `ProductType` class is actually a **service**. You can therefore inject dependencies in it.

<div class="alert alert-warning"><strong>Heads up!</strong> The <code>ProductType</code> class must exist in the container of your application and the container identifier MUST be the fully qualified class name.<br/><br/>
If you are using the Symfony bundle (or a framework with autowiring like Laravel), this 
is usually not an issue as the container will automatically create the controller entry if you do not explicitly 
declare it.</div> 

In methods with a `@Field` annotaiton, the first parameter is the *resolved object* we are working on. Any additional parameters are used as arguments.

## `@SourceField` annotation

If you don't want to rewrite all *getters* of your base class, you may use the `@SourceField` annotation:

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

By doing so, you let GraphQLite know that the type exposes the `getName` method of the underlying `Product` object.

Internally, GraphQLite will look for methods named `name()`, `getName()` and `isName()`).

### Authentication and authorization

You may also check for logged users or users with a specific right using the `logged` and `right` properties of the annotation:

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
     * Dynamically returns the array of source fields 
     * to be fetched from the original object.
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
