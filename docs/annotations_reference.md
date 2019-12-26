---
id: annotations_reference
title: Annotations reference
sidebar_label: Annotations reference
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
name           | *no*       | string | The name of the GraphQL type generated. If not passed, the name of the class is used. If the class ends with "Type", the "Type" suffix is removed
default        | *no*       | bool   | Defaults to *true*. Whether the targeted PHP class should be mapped by default to this type.
external       | *no*       | bool   | Whether this is an [external type declaration](external_type_declaration.md) or not. You usually do not need to use this attribute since this value defaults to true if a "class" attribute is set. This is only useful if you are declaring a type with no PHP class mapping using the "name" attribute.

## @ExtendType annotation

The `@ExtendType` annotation is used to add fields to an existing GraphQL object type.

**Applies on**: classes.

Attribute      | Compulsory | Type | Definition
---------------|------------|------|--------
class          | see below  | string | The targeted class. [The class annotated with `@ExtendType` is a service](extend_type.md).
name           | see below  | string | The targeted GraphQL output type.

One and only one of "class" and "name" parameter can be passed at the same time.

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
phpType        | *no*       | string | The PHP type of the field (as you would write it in a Docblock)
annotations    | *no*       | array<Annotations>  | A set of annotations that apply to this field. You would typically used a "@Logged" or "@Right" annotation here.

**Note**: `outputType` and `phpType` are mutually exclusive.

## @MagicField annotation

The `@MagicField` annotation is used to declare a GraphQL field that originates from a PHP magic property (using `__get` magic method).

**Applies on**: classes annotated with `@Type` or `@ExtendType`.

Attribute      | Compulsory | Type | Definition
---------------|------------|------|--------
name           | *yes*       | string | The name of the field.
[outputType](custom_output_types.md)  | *no*(*)       | string | The GraphQL output type of the field.
phpType     | *no*(*)       | string | The PHP type of the field (as you would write it in a Docblock)
annotations    | *no*       | array<Annotations>  | A set of annotations that apply to this field. You would typically used a "@Logged" or "@Right" annotation here.

(*) **Note**: `outputType` and `phpType` are mutually exclusive. You MUST provide one of them.

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

## @HideIfUnauthorized annotation

The `@HideIfUnauthorized` annotation is used to completely hide the query / mutation / field if the user is not authorized
to access it (according to the `@Logged` and `@Right` annotations).

**Applies on**: methods annotated with `@Query`, `@Mutation` or `@Field` and one of `@Logged` or `@Right` annotations.

`@HideIfUnauthorized` and `@FailWith` are mutually exclusive.

## @InjectUser annotation

Use the `@InjectUser` annotation to inject an instance of the current user logged in into a parameter of your 
query / mutation / field.

**Applies on**: methods annotated with `@Query`, `@Mutation` or `@Field`.

Attribute      | Compulsory | Type   | Definition
---------------|------------|--------|--------
*for*          | *yes*      | string | The name of the PHP parameter

## @Security annotation

The `@Security` annotation can be used to check fin-grained access rights.
It is very flexible: it allows you to pass an expression that can contains custom logic.

See [the fine grained security page](fine-grained-security.md) for more details.

**Applies on**: methods annotated with `@Query`, `@Mutation` or `@Field`.

Attribute      | Compulsory | Type   | Definition
---------------|------------|--------|--------
*default*      | *yes*      | string | The security expression

## @Factory annotation

The `@Factory` annotation is used to declare a factory that turns GraphQL input types into objects.

**Applies on**: methods from classes in the "types" namespace.

Attribute      | Compulsory | Type | Definition
---------------|------------|------|--------
name           | *no*       | string | The name of the input type. If skipped, the name of class returned by the factory is used instead.
default        | *no*       | bool | If `true`, this factory will be used by default for its PHP return type. If set to `false`, you must explicitly [reference this factory using the `@Parameter` annotation](http://localhost:3000/docs/input-types#declaring-several-input-types-for-the-same-php-class).

## @UseInputType annotation

Used to override the GraphQL input type of a PHP parameter.

**Applies on**: methods annotated with `@Query`, `@Mutation` or `@Field` annotation.

Attribute      | Compulsory | Type | Definition
---------------|------------|------|--------
*for*          | *yes*      | string | The name of the PHP parameter
*inputType*    | *yes*      | string | The GraphQL input type to force for this input field

## @Decorate annotation

The `@Decorate` annotation is used [to extend/modify/decorate an input type declared with the `@Factory` annotation](extend_input_type.md).

**Applies on**: methods from classes in the "types" namespace.

Attribute      | Compulsory | Type | Definition
---------------|------------|------|--------
name           | *yes*      | string | The GraphQL input type name extended by this decorator.

## @Autowire annotation

[Resolves a PHP parameter from the container](autowiring.md).

Useful to inject services directly into `@Field` method arguments.

**Applies on**: methods annotated with `@Query`, `@Mutation` or `@Field` annotation.

Attribute      | Compulsory | Type | Definition
---------------|------------|------|--------
*for*          | *yes*      | string | The name of the PHP parameter
*identifier*   | *no*       | string | The identifier of the service to fetch. This is optional. Please avoid using this attribute as this leads to a "service locator" anti-pattern.

## @HideParameter annotation

Removes [an argument from the GraphQL schema](input_types.md#ignoring_some_parameters).

Attribute      | Compulsory | Type | Definition
---------------|------------|------|--------
*for*          | *yes*      | string | The name of the PHP parameter to hide


## @Validate annotation

<div class="alert alert-info">This annotation is only available in the GraphQLite Laravel package</div>

[Validates a user input in Laravel](laravel-package-advanced.md).

**Applies on**: methods annotated with `@Query`, `@Mutation`, `@Field`, `@Factory` or `@Decorator` annotation.

Attribute      | Compulsory | Type | Definition
---------------|------------|------|--------
*for*          | *yes*      | string | The name of the PHP parameter
*rule*         | *yes       | string | Laravel validation rules

Sample:

```
@Validate(for="$email", rule="email|unique:users")
```

## @Assertion annotation

[Validates a user input](validation.md).

The `@Assertion` annotation  is available in the *thecodingmachine/graphqlite-symfony-validator-bridge* third party package.
It is available out of the box if you use the Symfony bundle.

**Applies on**: methods annotated with `@Query`, `@Mutation`, `@Field`, `@Factory` or `@Decorator` annotation.

Attribute      | Compulsory | Type | Definition
---------------|------------|------|--------
*for*          | *yes*      | string | The name of the PHP parameter
*constraint*   | *yes       | annotation | One (or many) Symfony validation annotations.
