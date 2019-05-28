---
id: version-3.0-annotations_reference
title: Annotations reference
sidebar_label: Annotations reference
original_id: annotations_reference
---

## @Query annotation

The `@Query` annotation is used to declare a GraphQL query.

**Applies on**: controller methods.

Attribute      | Compulsory | Type | Definition
---------------|------------|------|--------
name           | *no*       | string | The name of the query. If skipped, the name of the method is used instead.
[outputType](custom_output_types.md)     | *no*       | string | Forces the GraphQL output type of a query.

## @Mutation annotation

The `@Mutation` annotation is used to declare a GraphQL mutation.

**Applies on**: controller methods.

Attribute      | Compulsory | Type | Definition
---------------|------------|------|--------
name           | *no*       | string | The name of the mutation. If skipped, the name of the method is used instead.
[outputType](custom_output_types.md)     | *no*       | string | Forces the GraphQL output type of a query.

## @Type annotation

The `@Type` annotation is used to declare a GraphQL object type.

**Applies on**: classes.

Attribute      | Compulsory | Type | Definition
---------------|------------|------|--------
class          | *no*       | string | The targeted class. If no class is passed, the type applies to the current class. The current class is assumed to be an entity. If the "class" attribute is passed, [the class annotated with `@Type` is a service](external_type_declaration.md).


## @ExtendType annotation

The `@ExtendType` annotation is used to add fields to an existing GraphQL object type.

**Applies on**: classes.

Attribute      | Compulsory | Type | Definition
---------------|------------|------|--------
class          | *yes*       | string | The targeted class. [The class annotated with `@ExtendType` is a service](extend_type.md).

## @Field annotation

The `@Field` annotation is used to declare a GraphQL field.

**Applies on**: methods of classes annotated with `@Type` or `@ExtendType`.

Attribute      | Compulsory | Type | Definition
---------------|------------|------|--------
name           | *no*       | string | The name of the field. If skipped, the name of the method is used instead.
[outputType](custom_output_types.md)     | *no*       | string | Forces the GraphQL output type of a query.

## @SourceField annotation

The `@SourceField` annotation is used to declare a GraphQL field.

**Applies on**: classes annotated with `@Type` or `@ExtendType`.

Attribute      | Compulsory | Type | Definition
---------------|------------|------|--------
name           | *yes*       | string | The name of the field.
[outputType](custom_output_types.md)     | *no*       | string | Forces the GraphQL output type of the field. Otherwise, return type is used.
logged         | *no*       | bool  | Whether the user must be logged or not to see the field.
right          | *no*       | Right annotation  | The right the user must have to see the field.
failWith          | *no*       | mixed  | A value to return if the user is not authorized to see the field. If not specified, the field will not be visible at all to the user.


## @Logged annotation

The `@Logged` annotation is used to declare a Query/Mutation/Field is only visible to logged users.

**Applies on**: methods annotated with `@Query`, `@Mutation` or `@Field`.

This annotation allows no attributes.

## @Right annotation

The `@Right` annotation is used to declare a Query/Mutation/Field is only visible to users with a specific right.

**Applies on**: methods annotated with `@Query`, `@Mutation` or `@Field`.

Attribute      | Compulsory | Type | Definition
---------------|------------|------|--------
name           | *yes*       | string | The name of the right.

## @FailWith annotation

The `@FailWith` annotation is used to declare a default value to return in the user is not authorized to see a specific
query / mutation / field (according to the `@Logged` and `@Right` annotations).

**Applies on**: methods annotated with `@Query`, `@Mutation` or `@Field` and one of `@Logged` or `@Right` annotations.

Attribute      | Compulsory | Type | Definition
---------------|------------|------|--------
*default*      | *yes*       | mixed | The value to return if the user is not authorized.

## @Factory annotation

The `@Factory` annotation is used to declare a factory that turns GraphQL input types into objects.

**Applies on**: methods from classes in the "types" namespace.

Attribute      | Compulsory | Type | Definition
---------------|------------|------|--------
name           | *no*       | string | The name of the input type. If skipped, the name of class returned by the factory is used instead.

