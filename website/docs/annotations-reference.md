---
id: annotations-reference
title: Annotations reference
sidebar_label: Annotations reference
---

Note: all annotations are available both in a Doctrine annotation format (`@Query`) and in PHP 8 attribute format (`#[Query]`).
See [Doctrine annotations vs PHP 8 attributes](doctrine-annotations-attributes.mdx) for more details.

## @Query

The `@Query` annotation is used to declare a GraphQL query.

**Applies on**: controller methods.

Attribute      | Compulsory | Type | Definition
---------------|------------|------|--------
name           | *no*       | string | The name of the query. If skipped, the name of the method is used instead.
[outputType](custom-types.mdx)     | *no*       | string | Forces the GraphQL output type of a query.

## @Mutation

The `@Mutation` annotation is used to declare a GraphQL mutation.

**Applies on**: controller methods.

Attribute      | Compulsory | Type | Definition
---------------|------------|------|--------
name           | *no*       | string | The name of the mutation. If skipped, the name of the method is used instead.
[outputType](custom-types.mdx)     | *no*       | string | Forces the GraphQL output type of a query.

## @Type

The `@Type` annotation is used to declare a GraphQL object type.  This is used with standard output
types, as well as enum types.  For input types, use the [@Input annotation](#input-annotation) directly on the input type or a [@Factory annoation](#factory-annotation) to make/return an input type.

**Applies on**: classes.

Attribute      | Compulsory | Type | Definition
---------------|------------|------|--------
class          | *no*       | string | The targeted class/enum for the actual type. If no "class" attribute is passed, the type applies to the current class/enum. The current class/enum is assumed to be an entity (not service). If the "class" attribute *is passed*, [the class/enum annotated with `@Type` becomes a service](external-type-declaration.mdx).
name           | *no*       | string | The name of the GraphQL type generated. If not passed, the name of the class is used. If the class ends with "Type", the "Type" suffix is removed
default        | *no*       | bool   | Defaults to *true*. Whether the targeted PHP class should be mapped by default to this type.
external       | *no*       | bool   | Whether this is an [external type declaration](external-type-declaration.mdx) or not. You usually do not need to use this attribute since this value defaults to true if a "class" attribute is set. This is only useful if you are declaring a type with no PHP class mapping using the "name" attribute.

## @ExtendType

The `@ExtendType` annotation is used to add fields to an existing GraphQL object type.

**Applies on**: classes.

Attribute      | Compulsory | Type | Definition
---------------|------------|------|--------
class          | see below  | string | The targeted class. [The class annotated with `@ExtendType` is a service](extend-type.mdx).
name           | see below  | string | The targeted GraphQL output type.

One and only one of "class" and "name" parameter can be passed at the same time.

## @Input

The `@Input` annotation is used to declare a GraphQL input type.

**Applies on**: classes.

Attribute      | Compulsory | Type   | Definition
---------------|------------|--------|--------
name           | *no*       | string | The name of the GraphQL input type generated. If not passed, the name of the class with suffix "Input" is used. If the class ends with "Input", the "Input" suffix is not added.
description    | *no*       | string | Description of the input type in the documentation. If not passed, PHP doc comment is used.
default        | *no*       | bool   | Name of the input type represented in your GraphQL schema. Defaults to `true` *only if* the name is not specified. If `name` is specified, this will default to `false`, so must also be included for `true` when `name` is used.
update         | *no*       | bool   | Determines if the the input represents a partial update. When set to `true` all input fields will become optional and won't have default values thus won't be set on resolve if they are not specified in the query/mutation.  This primarily applies to nullable fields.

## @Field

The `@Field` annotation is used to declare a GraphQL field.

**Applies on**: methods or properties of classes annotated with `@Type`, `@ExtendType` or `@Input`.
When it's applied on private or protected property, public getter or/and setter method is expected in the class accordingly
whether it's used for output type or input type. For example if property name is `foo` then getter should be `getFoo()` or setter should be `setFoo($foo)`. Setter can be omitted if property related to the field is present in the constructor with the same name.

Attribute                     | Compulsory | Type | Definition
------------------------------|------------|---------------|--------
name                          | *no*       | string        | The name of the field. If skipped, the name of the method is used instead.
for                           | *no*       | string, array | Forces the field to be used only for specific output or input type(s). By default field is used for all possible declared types.
description                   | *no*       | string        | Field description displayed in the GraphQL docs. If it's empty PHP doc comment is used instead.
[outputType](custom-types.mdx) | *no*       | string        | Forces the GraphQL output type of a query.
[inputType](input-types.mdx)   | *no*       | string        | Forces the GraphQL input type of a query.

## @SourceField

The `@SourceField` annotation is used to declare a GraphQL field.

**Applies on**: classes annotated with `@Type` or `@ExtendType`.

Attribute      | Compulsory | Type | Definition
---------------|------------|------|--------
name           | *yes*       | string | The name of the field.
[outputType](custom-types.mdx)     | *no*       | string | Forces the GraphQL output type of the field. Otherwise, return type is used.
phpType        | *no*       | string | The PHP type of the field (as you would write it in a Docblock)
description    | *no*       | string | Field description displayed in the GraphQL docs. If it's empty PHP doc comment of the method in the source class is used instead.
sourceName     | *no*       | string | The name of the property in the source class. If not set, the `name` will be used to get property value.
annotations    | *no*       | array\<Annotations\>  | A set of annotations that apply to this field. You would typically used a "@Logged" or "@Right" annotation here. Available in Doctrine annotations only (not available in the #SourceField PHP 8 attribute)

**Note**: `outputType` and `phpType` are mutually exclusive.

## @MagicField

The `@MagicField` annotation is used to declare a GraphQL field that originates from a PHP magic property (using `__get` magic method).

**Applies on**: classes annotated with `@Type` or `@ExtendType`.

Attribute      | Compulsory | Type | Definition
---------------|------------|------|--------
name           | *yes*       | string | The name of the field.
[outputType](custom-types.mdx)  | *no*(*)       | string | The GraphQL output type of the field.
phpType     | *no*(*)       | string | The PHP type of the field (as you would write it in a Docblock)
description    | *no*       | string | Field description displayed in the GraphQL docs. If not set, no description will be shown.
sourceName     | *no*       | string | The name of the property in the source class. If not set, the `name` will be used to get property value.
annotations    | *no*       | array\<Annotations\>  | A set of annotations that apply to this field. You would typically used a "@Logged" or "@Right" annotation here. Available in Doctrine annotations only (not available in the #MagicField PHP 8 attribute)

(*) **Note**: `outputType` and `phpType` are mutually exclusive. You MUST provide one of them.

## @Logged

The `@Logged` annotation is used to declare a Query/Mutation/Field is only visible to logged users.

**Applies on**: methods or properties annotated with `@Query`, `@Mutation` or `@Field`.

This annotation allows no attributes.

## @Right

The `@Right` annotation is used to declare a Query/Mutation/Field is only visible to users with a specific right.

**Applies on**: methods or properties annotated with `@Query`, `@Mutation` or `@Field`.

Attribute      | Compulsory | Type | Definition
---------------|------------|------|--------
name           | *yes*       | string | The name of the right.

## @FailWith

The `@FailWith` annotation is used to declare a default value to return in the user is not authorized to see a specific
query / mutation / field (according to the `@Logged` and `@Right` annotations).

**Applies on**: methods or properties annotated with `@Query`, `@Mutation` or `@Field` and one of `@Logged` or `@Right` annotations.

Attribute      | Compulsory | Type | Definition
---------------|------------|------|--------
value          | *yes*       | mixed | The value to return if the user is not authorized.

## @HideIfUnauthorized

<div class="alert alert--warning">This annotation only works when a Schema is used to handle exactly one use request. 
If you serve your GraphQL API from long-running standalone servers (like Laravel Octane, Swoole, RoadRunner etc) and 
share the same Schema instance between multiple requests, please avoid using @HideIfUnauthorized.</div>

The `@HideIfUnauthorized` annotation is used to completely hide the query / mutation / field if the user is not authorized
to access it (according to the `@Logged` and `@Right` annotations).

**Applies on**: methods or properties annotated with `@Query`, `@Mutation` or `@Field` and one of `@Logged` or `@Right` annotations.

`@HideIfUnauthorized` and `@FailWith` are mutually exclusive.

## @InjectUser

Use the `@InjectUser` annotation to inject an instance of the current user logged in into a parameter of your
query / mutation / field.

See [the authentication and authorization page](authentication-authorization.mdx) for more details.

**Applies on**: methods annotated with `@Query`, `@Mutation` or `@Field`.

Attribute      | Compulsory | Type   | Definition
---------------|------------|--------|--------
*for*          | *yes*      | string | The name of the PHP parameter

## @Security

The `@Security` annotation can be used to check fin-grained access rights.
It is very flexible: it allows you to pass an expression that can contains custom logic.

See [the fine grained security page](fine-grained-security.mdx) for more details.

**Applies on**: methods or properties annotated with `@Query`, `@Mutation` or `@Field`.

Attribute      | Compulsory | Type   | Definition
---------------|------------|--------|--------
*default*      | *yes*      | string | The security expression

## @Factory

The `@Factory` annotation is used to declare a factory that turns GraphQL input types into objects.

**Applies on**: methods from classes in the "types" namespace.

Attribute      | Compulsory | Type | Definition
---------------|------------|------|--------
name           | *no*       | string | The name of the input type. If skipped, the name of class returned by the factory is used instead.
default        | *no*       | bool | If `true`, this factory will be used by default for its PHP return type. If set to `false`, you must explicitly [reference this factory using the `@Parameter` annotation](input-types.mdx#declaring-several-input-types-for-the-same-php-class).

## @UseInputType

Used to override the GraphQL input type of a PHP parameter.

**Applies on**: methods annotated with `@Query`, `@Mutation` or `@Field` annotation.

Attribute      | Compulsory | Type | Definition
---------------|------------|------|--------
*for*          | *yes*      | string | The name of the PHP parameter
*inputType*    | *yes*      | string | The GraphQL input type to force for this input field

## @Decorate

The `@Decorate` annotation is used [to extend/modify/decorate an input type declared with the `@Factory` annotation](extend-input-type.mdx).

**Applies on**: methods from classes in the "types" namespace.

Attribute      | Compulsory | Type | Definition
---------------|------------|------|--------
name           | *yes*      | string | The GraphQL input type name extended by this decorator.

## @Autowire

[Resolves a PHP parameter from the container](autowiring.mdx).

Useful to inject services directly into `@Field` method arguments.

**Applies on**: methods annotated with `@Query`, `@Mutation` or `@Field` annotation.

Attribute      | Compulsory | Type | Definition
---------------|------------|------|--------
*for*          | *yes*      | string | The name of the PHP parameter
*identifier*   | *no*       | string | The identifier of the service to fetch. This is optional. Please avoid using this attribute as this leads to a "service locator" anti-pattern.

## @HideParameter

Removes [an argument from the GraphQL schema](input-types.mdx#ignoring-some-parameters).

Attribute      | Compulsory | Type | Definition
---------------|------------|------|--------
*for*          | *yes*      | string | The name of the PHP parameter to hide

## @Validate

<div class="alert alert--info">This annotation is only available in the GraphQLite Laravel package</div>

[Validates a user input in Laravel](laravel-package-advanced.mdx).

**Applies on**: methods annotated with `@Query`, `@Mutation`, `@Field`, `@Factory` or `@Decorator` annotation.

Attribute      | Compulsory | Type | Definition
---------------|------------|------|--------
*for*          | *yes*      | string | The name of the PHP parameter
*rule*         | *yes       | string | Laravel validation rules

Sample:

```php
@Validate(for="$email", rule="email|unique:users")
```

## @Assertion

[Validates a user input](validation.mdx).

The `@Assertion` annotation  is available in the *thecodingmachine/graphqlite-symfony-validator-bridge* third party package.
It is available out of the box if you use the Symfony bundle.

**Applies on**: methods annotated with `@Query`, `@Mutation`, `@Field`, `@Factory` or `@Decorator` annotation.

Attribute      | Compulsory | Type | Definition
---------------|------------|------|--------
*for*          | *yes*      | string | The name of the PHP parameter
*constraint*   | *yes       | annotation | One (or many) Symfony validation annotations.

## ~~@EnumType~~

*Deprecated: Use [PHP 8.1's native Enums](https://www.php.net/manual/en/language.types.enumerations.php) instead with a [@Type](#type-annotation).*

The `@EnumType` annotation is used to change the name of a "Enum" type.
Note that if you do not want to change the name, the annotation is optionnal. Any object extending `MyCLabs\Enum\Enum`
is automatically mapped to a GraphQL enum type.

**Applies on**: classes extending the `MyCLabs\Enum\Enum` base class.

Attribute      | Compulsory | Type | Definition
---------------|------------|------|--------
name           | *no*       | string | The name of the enum type (in the GraphQL schema)
