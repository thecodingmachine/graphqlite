---
id: custom-output-types
title: Custom output types
sidebar_label: Custom output types
---

## Why do I need this?

In some special cases, you want to override the GraphQL return type that is attributed by default by GraphQL-Controllers.

Here is a sample:

```php
/**
 * @Type(class=Product::class)
 */
class ProductType
{
    /**
     * @Field(name="id")
     */
    public function getId(Product $source): string
    {
        return $source->getId();
    }
}
```

In the example above, GraphQL-Controllers will generate a GraphQL schema with a field "id" of type "string".

```graphql
type Product {
    id: String!
}
```

GraphQL comes with an "ID" scalar type. But PHP has no such type. So GraphQL-Controllers does not know when a variable
is an ID or not.

You can help GraphQL-Controllers by manually specifying the output type to use:

```php
    /**
     * @Field(name="id", outputType="ID")
     */
``` 

## Usage

The **outputType** attribute will map the return value of the method to the output type passed in parameter.

You can use the **outputType** attribute in the following annotations:

- `@Query`
- `@Mutation`
- `@Field`
- `@SourceField`

## Registering a custom output type (advanced)

If you have special needs, you can design your own output type. GraphQL-Controllers runs on top of webonyx/graphql.

In order to create a custom output type, you need to:

1. design a class that extends `GraphQL\Type\Definition\ObjectType`
2. register this class in the GraphQL schema

In order to [create your custom output type, check out the Webonyx documentation](https://webonyx.github.io/graphql-php/type-system/object-types/).

In order to find existing types, the schema is using "type mappers" (classes implementing the `TypeMapperInterface` interface).
You need to make sure that one of these type mappers can return an instance of your type. The way you do this will depend on the framework
you use.

### Symfony users

TODO

### Other frameworks

The easiest way is to use a `StaticTypeMapper`. This class is used to register custom output types.

```php
// Sample code:
$staticTypeMapper = new StaticTypeMapper();

// Let's register a type that maps by default to the "MyClass" PHP class
$staticTypeMapper->setTypes([
    MyClass::class => new MyCustomOutputType()
]);

// If you don't want your output type to map to any PHP class by default, use:
$staticTypeMapper->setNotMappedTypes([
    new MyCustomOutputType()
]);

```

**Notice:** The `StaticTypeMapper` instance must be registered in your container and linked to a `CompositeTypeMapper`
that will aggregate all the type mappers of the application.