---
id: version-4.0-custom-types
title: Custom types
sidebar_label: Custom types
original_id: custom-types
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
     * @Field(name="id", outputType="ID")
     */
``` 

## Usage

The `outputType` attribute will map the return value of the method to the output type passed in parameter.

You can use the `outputType` attribute in the following annotations:

* `@Query`
* `@Mutation`
* `@Field`
* `@SourceField`
* `@MagicField`

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

## Registering a custom scalar type (advanced)

If you need to add custom scalar types, first, check the [GraphQLite Misc. Types library](https://github.com/thecodingmachine/graphqlite-misc-types).
It contains a number of "out-of-the-box" scalar types ready to use and you might find what you need there.

You still need to develop your custom scalar type? Ok, let's get started.

In order to add a scalar type in GraphQLite, you need to:

- create a [Webonyx custom scalar type](https://webonyx.github.io/graphql-php/type-system/scalar-types/#writing-custom-scalar-types).
  You do this by creating a class that extends `GraphQL\Type\Definition\ScalarType`.
- create a "type mapper" that will map PHP types to the GraphQL scalar type. You do this by writing a class implementing the `RootTypeMapperInterface`.
- create a "type mapper factory" that will be in charge of creating your "type mapper".

```php
interface RootTypeMapperInterface
{
    public function toGraphQLOutputType(Type $type, ?OutputType $subType, ReflectionMethod $refMethod, DocBlock $docBlockObj): OutputType;

    public function toGraphQLInputType(Type $type, ?InputType $subType, string $argumentName, ReflectionMethod $refMethod, DocBlock $docBlockObj): InputType;

    public function mapNameToType(string $typeName): NamedType;
}
```

The `toGraphQLOutputType` and `toGraphQLInputType` are meant to map a return type (for output types) or a parameter type (for input types)
to your GraphQL scalar type. Return your scalar type if there is a match or `null` if there no match.

The `mapNameToType` should return your GraphQL scalar type if `$typeName` is the name of your scalar type.

RootTypeMapper are organized **in a chain** (they are actually middlewares).
Each instance of a `RootTypeMapper` holds a reference on the next root type mapper to be called in the chain.

For instance:

```php
class AnyScalarTypeMapper implements RootTypeMapperInterface
{
    /** @var RootTypeMapperInterface */
    private $next;

    public function __construct(RootTypeMapperInterface $next)
    {
        $this->next = $next;
    }

    public function toGraphQLOutputType(Type $type, ?OutputType $subType, ReflectionMethod $refMethod, DocBlock $docBlockObj): ?OutputType
    {
        if ($type instanceof Scalar) {
            // AnyScalarType is a class implementing the Webonyx ScalarType type.
            return AnyScalarType::getInstance();
        }
        // If the PHPDoc type is not "Scalar", let's pass the control to the next type mapper in the chain
        return $this->next->toGraphQLOutputType($type, $subType, $refMethod, $docBlockObj);
    }

    public function toGraphQLInputType(Type $type, ?InputType $subType, string $argumentName, ReflectionMethod $refMethod, DocBlock $docBlockObj): ?InputType
    {
        if ($type instanceof Scalar) {
            // AnyScalarType is a class implementing the Webonyx ScalarType type.
            return AnyScalarType::getInstance();
        }
        // If the PHPDoc type is not "Scalar", let's pass the control to the next type mapper in the chain
        return $this->next->toGraphQLInputType($type, $subType, $argumentName, $refMethod, $docBlockObj);
    }

    /**
     * Returns a GraphQL type by name.
     * If this root type mapper can return this type in "toGraphQLOutputType" or "toGraphQLInputType", it should
     * also map these types by name in the "mapNameToType" method.
     *
     * @param string $typeName The name of the GraphQL type
     * @return NamedType|null
     */
    public function mapNameToType(string $typeName): ?NamedType
    {
        if ($typeName === AnyScalarType::NAME) {
            return AnyScalarType::getInstance();
        }
        return null;
    }
}
```

Now, in order to create an instance of your `AnyScalarTypeMapper` class, you need an instance of the `$next` type mapper in the chain.
How do you get the `$next` type mapper? Through a factory:

```php
class AnyScalarTypeMapperFactory implements RootTypeMapperFactoryInterface
{
    public function create(RootTypeMapperInterface $next, RootTypeMapperFactoryContext $context): RootTypeMapperInterface
    {
        return new AnyScalarTypeMapper($next);
    }
}
```

Now, you need to register this factory in your application, and we are done.

You can register your own root mapper factories using the `SchemaFactory::addRootTypeMapperFactory()` method.

```php
$schemaFactory->addRootTypeMapperFactory(new AnyScalarTypeMapperFactory());
```
 
If you are using the Symfony bundle, the factory will be automatically registered, you have nothing to do (the service 
is automatically tagged with the "graphql.root_type_mapper_factory" tag).
