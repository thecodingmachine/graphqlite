---
id: internals
title: Internals
sidebar_label: Internals
---
<script src="https://unpkg.com/mermaid@8.0.0/dist/mermaid.min.js"></script>

## Mapping types

The core of GraphQLite is its ability to map PHP types to GraphQL types. This mapping is performed by a series of
"type mappers".

GraphQLite contains 3 categories of type mappers:

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
    YourCustomRootTypeMapper-->MyCLabsEnumTypeMapper
    MyCLabsEnumTypeMapper-->BaseTypeMapper
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
 - mapping list types (mapping a PHP array to a GraphQL list)
 - mapping enums

Root type mappers have access to the *context* of a type: they can access the PHP DocBlock and read annotations.
If you want to write a custom type mapper that needs access to annotations, it needs to be a "root type mapper".

GraphQLite provide 3 default implementations:

 - `CompositeRootTypeMapper`: a type mapper that delegates mapping to other type mappers using the Composite Design Pattern.
 - `MyCLabsEnumTypeMapper`: maps MyCLabs/enum types to GraphQL enum types
 - `BaseTypeMapper`: maps scalar types and lists. Passes the control to the "recursive type mappers" if an object is encountered.

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
