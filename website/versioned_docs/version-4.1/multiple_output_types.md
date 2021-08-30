---
id: multiple_output_types
title: Mapping multiple output types for the same class
sidebar_label: Class with multiple output types
original_id: multiple_output_types
---
<small>Available in GraphQLite 4.0+</small>

In most cases, you have one PHP class and you want to map it to one GraphQL output type.

But in very specific cases, you may want to use different GraphQL output type for the same class.
For instance, depending on the context, you might want to prevent the user from accessing some fields of your object.

To do so, you need to create 2 output types for the same PHP class. You typically do this using the "default" attribute of the `@Type` annotation.

## Example

Here is an example. Say we are manipulating products. When I query a `Product` details, I want to have access to all fields.
But for some reason, I don't want to expose the price field of a product if I query the list of all products.


<!--DOCUSAURUS_CODE_TABS-->
<!--PHP 8+-->

```php
#[Type]
class Product
{
    // ...

    #[Field]
    public function getName(): string
    {
        return $this->name;
    }

    #[Field]
    public function getPrice(): ?float
    {
        return $this->price;
    }
}
```

<!--PHP 7+-->

```php
/**
 * @Type()
 */
class Product
{
    // ...

    /**
     * @Field()
     */
    public function getName(): string
    {
        return $this->name;
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

<!--END_DOCUSAURUS_CODE_TABS-->

The `Product` class is declaring a classic GraphQL output type named "Product".

<!--DOCUSAURUS_CODE_TABS-->
<!--PHP 8+-->
```php
#[Type(class: Product::class, name: "LimitedProduct", default: false)]
#[SourceField(name: "name")]
class LimitedProductType
{
    // ...
}
```
<!--PHP 7+-->
```php
/**
 * @Type(class=Product::class, name="LimitedProduct", default=false)
 * @SourceField(name="name")
 */
class LimitedProductType
{
    // ...
}
```
<!--END_DOCUSAURUS_CODE_TABS-->



The `LimitedProductType` also declares an ["external" type](external_type_declaration.md) mapping the `Product` class.
But pay special attention to the `@Type` annotation.

First of all, we specify `name="LimitedProduct"`. This is useful to avoid having colliding names with the "Product" GraphQL output type
that is already declared.

Then, we specify `default=false`. This means that by default, the `Product` class should not be mapped to the `LimitedProductType`.
This type will only be used when we explicitly request it.

Finally, we can write our requests:

<!--DOCUSAURUS_CODE_TABS-->
<!--PHP 8+-->
```php
class ProductController
{
    /**
     * This field will use the default type.
     */
    #[Field]
    public function getProduct(int $id): Product { /* ... */ } 
    
    /**
     * Because we use the "outputType" attribute, this field will use the other type.
     *
     * @return Product[]
     */
    #[Field(outputType: "[LimitedProduct!]!")]
    public function getProducts(): array { /* ... */ } 
}
``` 
<!--PHP 7+-->
```php
class ProductController
{
    /**
     * This field will use the default type.
     *
     * @Field
     */
    public function getProduct(int $id): Product { /* ... */ } 
    
    /**
     * Because we use the "outputType" attribute, this field will use the other type.
     *
     * @Field(outputType="[LimitedProduct!]!")
     * @return Product[]
     */
    public function getProducts(): array { /* ... */ } 
}
```
<!--END_DOCUSAURUS_CODE_TABS-->



Notice how the "outputType" attribute is used in the `@Field` annotation to force the output type.

Is a result, when the end user calls the `product` query, we will have the possibility to fetch the `name` and `price` fields,
but if he calls the `products` query, each product in the list will have a `name` field but no `price` field. We managed
to successfully expose a different set of fields based on the query context.

## Extending a non-default type

If you want to extend a type using the `@ExtendType` annotation and if this type is declared as non-default,
you need to target the type by name instead of by class.

So instead of writing:

<!--DOCUSAURUS_CODE_TABS-->
<!--PHP 8+-->
```php
#[ExtendType(class: Product::class)]
```
<!--PHP 7+-->
```php
/**
 * @ExtendType(class=Product::class)
 */
```
<!--END_DOCUSAURUS_CODE_TABS-->

you will write:

<!--DOCUSAURUS_CODE_TABS-->
<!--PHP 8+-->
```php
#ExtendType(name: "LimitedProduct")
```
<!--PHP 7+-->
```php
/**
 * @ExtendType(name="LimitedProduct")
 */
```
<!--END_DOCUSAURUS_CODE_TABS-->

Notice how we use the "name" attribute instead of the "class" attribute in the `@ExtendType` annotation.
