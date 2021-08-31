---
id: changelog
title: Changelog
sidebar_label: Changelog
---

## 4.3.0

#### Breaking change:

- The method `setAnnotationCacheDir($directory)` has been removed from the `SchemaFactory`.  The annotation
  cache will use your `Psr\SimpleCache\CacheInterface` compliant cache handler set through the `SchemaFactory`
  constructor.

#### Minor changes:

- Removed dependency for doctrine/cache and unified some of the cache layers following a PSR interface.
- Cleaned up some of the documentation in an attempt to get things accurate with versioned releases.

## 4.2.0

#### Breaking change:

The method signature for `toGraphQLOutputType` and `toGraphQLInputType` have been changed to the following:

```php
/**
 * @param \ReflectionMethod|\ReflectionProperty $reflector
 */
public function toGraphQLOutputType(Type $type, ?OutputType $subType, $reflector, DocBlock $docBlockObj): OutputType;

/**
 * @param \ReflectionMethod|\ReflectionProperty $reflector
 */
public function toGraphQLInputType(Type $type, ?InputType $subType, string $argumentName, $reflector, DocBlock $docBlockObj): InputType;
```

#### New features:

- [@Input](annotations-reference.md#input-annotation) annotation is introduced as an alternative to `@Factory`. Now GraphQL input type can be created in the same manner as `@Type` in combination with `@Field` - [example](input-types.mdx#input-annotation).
- New attributes has been added to [@Field](annotations-reference.md#field-annotation) annotation: `for`, `inputType` and `description`.
- The following annotations now can be applied to class properties directly: `@Field`, `@Logged`, `@Right`, `@FailWith`, `@HideIfUnauthorized` and `@Security`.

## 4.1.0

#### Breaking change:

There is one breaking change introduced in the minor version (this was important to allow PHP 8 compatibility).

- The **ecodev/graphql-upload** package (used to get support for file uploads in GraphQL input types) is now a "recommended" dependency only.
  If you are using GraphQL file uploads, you need to add `ecodev/graphql-upload` to your `composer.json`.

#### New features:

- All annotations can now be accessed as PHP 8 attributes
- The `@deprecated` annotation in your PHP code translates into deprecated fields in your GraphQL schema
- You can now specify the GraphQL name of the Enum types you define
- Added the possibility to inject pure Webonyx objects in GraphQLite schema

#### Minor changes:

- Migrated from `zend/diactoros` to `laminas/diactoros`
- Making the annotation cache directory configurable

#### Miscellaneous:

- Migrated from Travis to Github actions


## 4.0.0

This is a complete refactoring from 3.x. While existing annotations are kept compatible, the internals have completely
changed.

#### New features:

- You can directly [annotate a PHP interface with `@Type` to make it a GraphQL interface](inheritance-interfaces.mdx#mapping-interfaces)
- You can autowire services in resolvers, thanks to the new `@Autowire` annotation
- Added [user input validation](validation.mdx) (using the Symfony Validator or the Laravel validator or a custom `@Assertion` annotation
- Improved security handling:
  - Unauthorized access to fields can now generate GraphQL errors (rather that schema errors in GraphQLite v3)
  - Added fine-grained security using the `@Security` annotation. A field can now be [marked accessible or not depending on the context](fine-grained-security.mdx).
    For instance, you can restrict access to the field "viewsCount" of the type `BlogPost` only for post that the current user wrote.
  - You can now inject the current logged user in any query / mutation / field using the `@InjectUser` annotation
- Performance:
  - You can inject the [Webonyx query plan in a parameter from a resolver](query-plan.mdx)
  - You can use the [dataloader pattern to improve performance drastically via the "prefetchMethod" attribute](prefetch-method.mdx)
- Customizable error handling has been added:
  - You can throw [many errors in one exception](error-handling.mdx#many-errors-for-one-exception) with `TheCodingMachine\GraphQLite\Exceptions\GraphQLAggregateException`
- You can force input types using `@UseInputType(for="$id", inputType="ID!")`
- You can extend an input types (just like you could extend an output type in v3) using [the new `@Decorate` annotation](extend-input-type.mdx)
- In a factory, you can [exclude some optional parameters from the GraphQL schema](input-types#ignoring-some-parameters)


Many extension points have been added

- Added a "root type mapper" (useful to map scalar types to PHP types or to add custom annotations related to resolvers)
- Added ["field middlewares"](field-middlewares.md) (useful to add middleware that modify the way GraphQL fields are handled)
- Added a ["parameter type mapper"](argument-resolving.md) (useful to add customize parameter resolution or add custom annotations related to parameters)

New framework specific features:

#### Symfony:

- The Symfony bundle now provides a "login" and a "logout" mutation (and also a "me" query)

#### Laravel:

- [Native integration with the Laravel paginator](laravel-package-advanced.mdx#support-for-pagination) has been added

#### Internals:

- The `FieldsBuilder` class has been split in many different services (`FieldsBuilder`, `TypeHandler`, and a
  chain of *root type mappers*)
- The `FieldsBuilderFactory` class has been completely removed.
- Overall, there is not much in common internally between 4.x and 3.x. 4.x is much more flexible with many more hook points
  than 3.x. Try it out!
