---
id: internals
title: Internals
sidebar_label: Internals
---
<script src="https://unpkg.com/mermaid@8.0.0/dist/mermaid.min.js"></script>

## Mapping types

The core of GraphQLite is its ability to map PHP types to GraphQL types. This mapping is performed by a series of
"type mappers".

GraphQLite contains 4 categories of type mappers:

- **Parameter mappers**
- **Root type mappers**
- **Recursive (class) type mappers**
- **(class) type mappers**


<script>
mermaid.initialize({
  theme: 'forest',
  // themeCSS: '.node rect { fill: red; }',
  logLevel: 3,
  flowchart: { curve: 'linear' },
  gantt: { axisFormat: '%m/%d/%Y' },
  sequence: { actorMargin: 50 },
});
</script>
<div class="mermaid">
  graph TD
  classDef custom fill:#cfc,stroke:#7a7,stroke-width:2px,stroke-dasharray: 5, 5;
  subgraph RootTypeMapperInterface
    NullableTypeMapperAdapter-->CompoundTypeMapper
    CompoundTypeMapper-->IteratorTypeMapper
    IteratorTypeMapper-->YourCustomRootTypeMapper
    YourCustomRootTypeMapper-->MyCLabsEnumTypeMapper
    MyCLabsEnumTypeMapper-->BaseTypeMapper
    BaseTypeMapper-->FinalRootTypeMapper
  end
  subgraph RecursiveTypeMapperInterface
    BaseTypeMapper-->RecursiveTypeMapper
  end
  subgraph TypeMapperInterface
    RecursiveTypeMapper-->YourCustomTypeMapper
    YourCustomTypeMapper-->PorpaginasTypeMapper
    PorpaginasTypeMapper-->GlobTypeMapper
  end
  class YourCustomRootTypeMapper,YourCustomTypeMapper custom;

</div>

## Root type mappers

(Classes implementing the [`RootTypeMapperInterface`](https://github.com/thecodingmachine/graphqlite/blob/master/src/Mappers/Root/RootTypeMapperInterface.php))

These type mappers are the first type mappers called.

They are responsible for:
 
 - mapping scalar types (for instance mapping the "int" PHP type to GraphQL Integer type)
 - detecting nullable/non-nullable types (for instance interpreting "?int" or "int|null")
 - mapping list types (mapping a PHP array to a GraphQL list)
 - mapping union types
 - mapping enums

Root type mappers have access to the *context* of a type: they can access the PHP DocBlock and read annotations.
If you want to write a custom type mapper that needs access to annotations, it needs to be a "root type mapper".

GraphQLite provides 6 classes implementing `RootTypeMapperInterface`:

 - `NullableTypeMapperAdapter`: a type mapper in charge of making GraphQL types non-nullable if the PHP type is non-nullable
 - `CompoundTypeMapper`: a type mapper in charge of union types
 - `IteratorTypeMapper`: a type mapper in charge of iterable types (for instance: `MyIterator|User[]`)
 - `MyCLabsEnumTypeMapper`: maps MyCLabs/enum types to GraphQL enum types
 - `BaseTypeMapper`: maps scalar types and lists. Passes the control to the "recursive type mappers" if an object is encountered.
 - `FinalRootTypeMapper`: the last type mapper of the chain, used to throw error if no other type mapper managed to handle the type.

Type mappers are organized in a chain; each type-mapper is responsible for calling the next type mapper.

<div class="mermaid">
  graph TD
  classDef custom fill:#cfc,stroke:#7a7,stroke-width:2px,stroke-dasharray: 5, 5;
  subgraph RootTypeMapperInterface
    NullableTypeMapperAdapter-->CompoundTypeMapper
    CompoundTypeMapper-->IteratorTypeMapper
    IteratorTypeMapper-->YourCustomRootTypeMapper
    YourCustomRootTypeMapper-->MyCLabsEnumTypeMapper
    MyCLabsEnumTypeMapper-->BaseTypeMapper
    BaseTypeMapper-->FinalRootTypeMapper
  end
  class YourCustomRootTypeMapper custom;
</div>


## Class type mappers

(Classes implementing the [`TypeMapperInterface`](https://github.com/thecodingmachine/graphqlite/blob/master/src/Mappers/TypeMapperInterface.php))

Class type mappers are mapping PHP classes to GraphQL object types.

GraphQLite provide 3 default implementations:

 - `CompositeTypeMapper`: a type mapper that delegates mapping to other type mappers using the Composite Design Pattern.
 - `GlobTypeMapper`: scans classes in a directory for the `@Type` or `@ExtendType` annotation and maps those to GraphQL types
 - `PorpaginasTypeMapper`: maps and class implementing the Porpaginas `Result` interface to a [special paginated type](pagination.md).

### Registering a type mapper in Symfony

If you are using the GraphQLite Symfony bundle, you can register a type mapper by tagging the service with the "graphql.type_mapper" tag.

### Registering a type mapper using the SchemaFactory

If you are using the `SchemaFactory` to bootstrap GraphQLite, you can register a type mapper using the `SchemaFactory::addTypeMapper` method.

## Recursive type mappers

(Classes implementing the [`RecursiveTypeMapperInterface`](https://github.com/thecodingmachine/graphqlite/blob/master/src/Mappers/RecursiveTypeMapperInterface.php))

There is only one implementation of the `RecursiveTypeMapperInterface`: the `RecursiveTypeMapper`.

Standard "class type mappers" are mapping a given PHP class to a GraphQL type. But they do not handle class hierarchies.
This is the role of the "recursive type mapper".

Imagine that class "B" extends class "A" and class "A" maps to GraphQL type "AType".

Since "B" *is a* "A", the "recursive type mapper" role is to make sure that "B" will also map to GraphQL type "AType". 

## Parameter mapper middlewares

"Parameter middlewares" are used to decide what argument should be injected into a parameter.

Let's have a look at a simple query:

```php
/**
 * @Query
 * @return Product[]
 */
public function products(ResolveInfo $info): array
```

As you may know, [the `ResolveInfo` object injected in this query comes from Webonyx/GraphQL-PHP library](query_plan.md).
GraphQLite knows that is must inject a `ResolveInfo` instance because it comes with a [`ResolveInfoParameterHandler`](https://github.com/thecodingmachine/graphqlite/blob/master/src/Mappers/Parameters/ResolveInfoParameterHandler.php) class
that implements the [`ParameterMiddlewareInterface`](https://github.com/thecodingmachine/graphqlite/blob/master/src/Mappers/Parameters/ParameterMiddlewareInterface.php)).

You can register your own parameter middlewares using the `SchemaFactory::addParameterMiddleware()` method, or by tagging the
service as "graphql.parameter_middleware" if you are using the Symfony bundle.

<div class="alert alert-info">Use a parameter middleware if you want to inject an argument in a method and if this argument
is not a GraphQL input type or if you want to alter the way input types are imported (for instance if you want to add a validation step)</div>
