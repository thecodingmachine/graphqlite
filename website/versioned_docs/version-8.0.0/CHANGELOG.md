---
id: changelog
title: Changelog
sidebar_label: Changelog
---

## 8.0.0

### Breaking Changes

- [#677 Drops support for Doctrine annotations](https://github.com/thecodingmachine/graphqlite/pull/677) @fogrye

### Improvements

- [#668 Adds working examples to docs](https://github.com/thecodingmachine/graphqlite/pull/668) @shish
- [#698 Performance optimizations and caching in development environments (`devMode()`)](https://github.com/thecodingmachine/graphqlite/pull/698) @oprypkhantc]

### Bug Fixes

- [#702 Fix prefetching for nested fields](https://github.com/thecodingmachine/graphqlite/pull/702) @sudevva

### Minor Changes

- [#695 Removes dependecy to unmaintained thecodingmachine/cache-utils dependency](https://github.com/thecodingmachine/graphqlite/pull/695) @xyng
- [#712 Caching improvements with use of multiple ClassFinders](https://github.com/thecodingmachine/graphqlite/pull/712) @andrew-demb

## 7.1.0

### Breaking Changes

- #698 Removes some methods and classes, namely:
  - Deprecated `SchemaFactory::addControllerNamespace()` and `SchemaFactory::addTypeNamespace()` in favor of `SchemaFactory::addNamespace()`
  - Deprecated `SchemaFactory::setGlobTTL()` in favor of `SchemaFactory::devMode()` and `SchemaFactory::prodMode()`
  - Removed `FactoryContext::get*TTL()` and `RootTypeMapperFactoryContext::get*TTL()` as GraphQLite no longer uses TTLs to invalidate caches
  - Removed `StaticClassListTypeMapper` in favor of `ClassFinderTypeMapper` used with `StaticClassFinder`
  - Renamed `GlobTypeMapper` to `ClassFinderTypeMapper`
  - Renamed `SchemaFactory::setClassBoundCacheContractFactory()` to `SchemaFactory::setClassBoundCache()`,
    `FactoryContext::getClassBoundCacheContractFactory()` to `FactoryContext::getClassBoundCache()` and changed their signatures
  - Removed `RootTypeMapperFactoryContext::getTypeNamespaces()` in favor of `RootTypeMapperFactoryContext::getClassFinder()`

### Improvements

- #698 Performance optimizations and caching in development environments (`devMode()`). @oprypkhantc

## 7.0.0

### Breaking Changes

- #664 Replaces [thecodingmachine/class-explorer](https://github.com/thecodingmachine/class-explorer) with [kcs/class-finder](https://github.com/alekitto/class-finder) resulting in the `SchemaFactory::setClassNameMapper` being renamed to `SchemaFactory::setFinder`.  This now expects an instance of `Kcs\ClassFinder\Finder` instead of `Kcs\ClassFinder\Finder\FinderInterface`. @fogrye

### New Features

- #649 Adds support for `subscription` operations. @oojacoboo
- #612 Automatic query complexity analysis. @oprypkhantc
- #611 Automatic persisted queries. @oprypkhantc

### Improvements

- #658 Improves on prefetching for nested fields. @grynchuk
- #646 Improves exception handling during schema parsing. @fogrye
- #636 Allows the use of middleware on construtor params/fields. @oprypkhantc
- #623 Improves support for description arguments on types/fields. @downace
- #628 Properly handles `@param` annotations for generics support on field annotated constructor arguments. @oojacoboo
- #584 Immutability improvements across the codebase. @oprypkhantc
- #588 Prefetch improvements. @oprpkhantc
- #606 Adds support for phpdoc descriptions and deprecation annotations on native enums. @mdoelker
- Thanks to @shish, @cvergne and @mshapovalov for updating the docs!

### Minor Changes

- #639 Added support for Symfony 7. @janatjak


## 6.2.3

Adds support for `Psr\Container` 1.1 with #601

## 6.2.2

This is a very simple release.  We support Doctrine annotation 1.x and we've deprecated `SchemaFactory::setDoctrineAnnotationReader` in favor of native PHP attributes.

## 6.2.1

- Added support for new `Void` return types, allowing use of `void` from operation resolvers. #574
- Improvements with authorization middleware #571
- Updated vendor dependencies: #580 #558

## 6.2.0

Lots of little nuggets in this release!  We're now targeting PHP ^8.1 and have testing on 8.2.

- Better support for union types and enums: #530, #535, #561, #570
- Various bug and interface fixes: #532, #575, #564
- GraphQL v15 required: #542
- Lots of codebase improvements, more strict typing: #548

A special thanks to @rusted-love and @oprypkhantc for their contributions.

## 6.1.0

A shoutout to @bladl for his work on this release, improving the code for better typing and PHP 8.0 syntax updates!

### Breaking Changes

- #518 PSR-11 support now requires version 2
- #508 Due to some of the code improvements, additional typing has been added to some interfaces/classes.  For instance, `RootTypeMapperInterface::toGraphQLOutputType` and `RootTypeMapperInterface::toGraphQLInputType` now have the following signatures:

```php
    /**
     * @param (OutputType&GraphQLType)|null $subType
     *
     * @return OutputType&GraphQLType
     */
    public function toGraphQLOutputType(
        Type $type,
        OutputType|null $subType,
        ReflectionMethod|ReflectionProperty $reflector,
        DocBlock $docBlockObj
    ): OutputType;

    /**
     * @param (InputType&GraphQLType)|null $subType
     *
     * @return InputType&GraphQLType
     */
    public function toGraphQLInputType(
        Type $type,
        InputType|null $subType,
        string $argumentName,
        ReflectionMethod|ReflectionProperty $reflector,
        DocBlock $docBlockObj
    ): InputType;
```

### Improvements

- #510
- #508

## 5.0.0

### Dependencies

- Upgraded to using version 14.9 of [webonyx/graphql-php](https://github.com/webonyx/graphql-php)

## 4.3.0

### Breaking change

- The method `setAnnotationCacheDir($directory)` has been removed from the `SchemaFactory`.  The annotation
  cache will use your `Psr\SimpleCache\CacheInterface` compliant cache handler set through the `SchemaFactory`
  constructor.

### Minor changes

- Removed dependency for doctrine/cache and unified some of the cache layers following a PSR interface.
- Cleaned up some of the documentation in an attempt to get things accurate with versioned releases.

## 4.2.0

### Breaking change

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

### New features

- [@Input](annotations-reference.md#input-annotation) annotation is introduced as an alternative to `#[Factory]`. Now GraphQL input type can be created in the same manner as `#[Type]` in combination with `#[Field]` - [example](input-types.mdx#input-attribute).
- New attributes has been added to [@Field](annotations-reference.md#field-annotation) annotation: `for`, `inputType` and `description`.
- The following annotations now can be applied to class properties directly: `#[Field]`, `#[Logged]`, `#[Right]`, `@FailWith`, `@HideIfUnauthorized` and `#[Security]`.

## 4.1.0

### Breaking change

There is one breaking change introduced in the minor version (this was important to allow PHP 8 compatibility).

- The **ecodev/graphql-upload** package (used to get support for file uploads in GraphQL input types) is now a "recommended" dependency only.
  If you are using GraphQL file uploads, you need to add `ecodev/graphql-upload` to your `composer.json`.

### New features

- All annotations can now be accessed as PHP 8 attributes
- The `@deprecated` annotation in your PHP code translates into deprecated fields in your GraphQL schema
- You can now specify the GraphQL name of the Enum types you define
- Added the possibility to inject pure Webonyx objects in GraphQLite schema

### Minor changes

- Migrated from `zend/diactoros` to `laminas/diactoros`
- Making the annotation cache directory configurable

### Miscellaneous

- Migrated from Travis to Github actions

## 4.0.0

This is a complete refactoring from 3.x. While existing annotations are kept compatible, the internals have completely
changed.

### New features

- You can directly [annotate a PHP interface with `#[Type]` to make it a GraphQL interface](inheritance-interfaces.mdx#mapping-interfaces)
- You can autowire services in resolvers, thanks to the new `@Autowire` annotation
- Added [user input validation](validation.mdx) (using the Symfony Validator or the Laravel validator or a custom `#[Assertion]` annotation
- Improved security handling:
  - Unauthorized access to fields can now generate GraphQL errors (rather that schema errors in GraphQLite v3)
  - Added fine-grained security using the `#[Security]` annotation. A field can now be [marked accessible or not depending on the context](fine-grained-security.mdx).
    For instance, you can restrict access to the field "viewsCount" of the type `BlogPost` only for post that the current user wrote.
  - You can now inject the current logged user in any query / mutation / field using the `#[InjectUser]` annotation
- Performance:
  - You can inject the [Webonyx query plan in a parameter from a resolver](query-plan.mdx)
  - You can use the [dataloader pattern to improve performance drastically via the "prefetchMethod" attribute](prefetch-method.mdx)
- Customizable error handling has been added:
  - You can throw [many errors in one exception](error-handling.mdx#many-errors-for-one-exception) with `TheCodingMachine\GraphQLite\Exceptions\GraphQLAggregateException`
- You can force input types using `@UseInputType(for="$id", inputType="ID!")`
- You can extend an input types (just like you could extend an output type in v3) using [the new `#[Decorate]` annotation](extend-input-type.mdx)
- In a factory, you can [exclude some optional parameters from the GraphQL schema](input-types#ignoring-some-parameters)

Many extension points have been added

- Added a "root type mapper" (useful to map scalar types to PHP types or to add custom annotations related to resolvers)
- Added ["field middlewares"](field-middlewares.md) (useful to add middleware that modify the way GraphQL fields are handled)
- Added a ["parameter type mapper"](argument-resolving.md) (useful to add customize parameter resolution or add custom annotations related to parameters)

New framework specific features:

### Symfony

- The Symfony bundle now provides a "login" and a "logout" mutation (and also a "me" query)

### Laravel

- [Native integration with the Laravel paginator](laravel-package-advanced.mdx#support-for-pagination) has been added

### Internals

- The `FieldsBuilder` class has been split in many different services (`FieldsBuilder`, `TypeHandler`, and a
  chain of *root type mappers*)
- The `FieldsBuilderFactory` class has been completely removed.
- Overall, there is not much in common internally between 4.x and 3.x. 4.x is much more flexible with many more hook points
  than 3.x. Try it out!
