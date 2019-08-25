---
id: version-3.0-custom-output-types
title: Custom output types
sidebar_label: Custom output types
original_id: custom-output-types
---

In some special cases, you want to override the GraphQL return type that is attributed by default by GraphQLite.

For instance:

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

In the example above, GraphQLite will generate a GraphQL schema with a field `id` of type `string`:

```graphql
type Product {
    id: String!
}
```

GraphQL comes with an `ID` scalar type. But PHP has no such type. So GraphQLite does not know when a variable
is an `ID` or not.

You can help GraphQLite by manually specifying the output type to use:

```php
    /**
     * @Field(name="id", outputType="ID!")
     */
```

## Usage

The `outputType` attribute will map the return value of the method to the output type passed in parameter.

You can use the `outputType` attribute in the following annotations:

* `@Query`
* `@Mutation`
* `@Field`
* `@SourceField`

## Registering a custom output type (advanced)

In order to create a custom output type, you need to:

1. Design a class that extends `GraphQL\Type\Definition\ObjectType`.
2. Register this class in the GraphQL schema.

You'll find more details on the [Webonyx documentation](https://webonyx.github.io/graphql-php/type-system/object-types/).

---

In order to find existing types, the schema is using *type mappers* (classes implementing the `TypeMapperInterface` interface).

You need to make sure that one of these type mappers can return an instance of your type. The way you do this will depend on the framework
you use.

### Symfony users

Any class extending `GraphQL\Type\Definition\ObjectType` (and available in the container) will be automatically detected 
by Symfony and added to the schema.

If you want to automatically map the output type to a given PHP class, you will have to explicitly declare the output type
as a service and use the `graphql.output_type` tag:

```yaml
# config/services.yaml
services:
    App\MyOutputType:
        tags:
            - { name: 'graphql.output_type', class: 'App\MyPhpClass' }
```

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

The `StaticTypeMapper` instance MUST be registered in your container and linked to a `CompositeTypeMapper`
that will aggregate all the type mappers of the application.