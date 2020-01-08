---
id: version-4.0-field-middlewares
title: Adding custom annotations with Field middlewares
sidebar_label: Custom annotations
original_id: field-middlewares
---
<small>Available in GraphQLite 4.0+</small>

Just like the `@Logged` or `@Right` annotation, you can develop your own annotation that extends/modifies the behaviour
of a field/query/mutation.

<div class="alert alert-warning">If you want to create an annotation that targets a single argument (like <code>@AutoWire(for="$service")</code>),
you should rather check the documentation about <a href="argument-resolving">custom argument resolving</a></div>

## Field middlewares

GraphQLite is based on the Webonyx/Graphql-PHP library. In Webonyx, fields are represented by the `FieldDefinition` class.
In order to create a `FieldDefinition` instance for your field, GraphQLite goes through a series of "middlewares".

![](assets/field_middleware.svg)

Each middleware is passed a `TheCodingMachine\GraphQLite\QueryFieldDescriptor` instance. This object contains all the
parameters used to initialize the field (like the return type, the list of arguments, the resolver to be used, etc...)

Each middleware must return a `GraphQL\Type\Definition\FieldDefinition` (the object representing a field in Webonyx/GraphQL-PHP).

```php
/**
 * Your middleware must implement this interface.
 */
interface FieldMiddlewareInterface
{
    public function process(QueryFieldDescriptor $queryFieldDescriptor, FieldHandlerInterface $fieldHandler): ?FieldDefinition;
}
```

```php
class QueryFieldDescriptor
{
    public function getName() { /* ... */ }
    public function setName(string $name)  { /* ... */ }
    public function getType() { /* ... */ }
    public function setType($type): void  { /* ... */ }
    public function getParameters(): array  { /* ... */ }
    public function setParameters(array $parameters): void  { /* ... */ }
    public function getPrefetchParameters(): array  { /* ... */ }
    public function setPrefetchParameters(array $prefetchParameters): void  { /* ... */ }
    public function getPrefetchMethodName(): ?string { /* ... */ }
    public function setPrefetchMethodName(?string $prefetchMethodName): void { /* ... */ }
    public function setCallable(callable $callable): void { /* ... */ }
    public function setTargetMethodOnSource(?string $targetMethodOnSource): void { /* ... */ }
    public function isInjectSource(): bool { /* ... */ }
    public function setInjectSource(bool $injectSource): void { /* ... */ }
    public function getComment(): ?string { /* ... */ }
    public function setComment(?string $comment): void { /* ... */ }
    public function getMiddlewareAnnotations(): MiddlewareAnnotations { /* ... */ }
    public function setMiddlewareAnnotations(MiddlewareAnnotations $middlewareAnnotations): void { /* ... */ }
    public function getOriginalResolver(): ResolverInterface { /* ... */ }
    public function getResolver(): callable { /* ... */ }
    public function setResolver(callable $resolver): void { /* ... */ }
}
```

The role of a middleware is to analyze the `QueryFieldDescriptor` and modify it (or to directly return a `FieldDefinition`).

If you want the field to purely disappear, your middleware can return `null`.

## Annotations parsing

Take a look at the `QueryFieldDescriptor::getMiddlewareAnnotations()`.

It returns the list of annotations applied to your field that implements the `MiddlewareAnnotationInterface`.

Let's imagine you want to add a `@OnlyDebug` annotation that displays a field/query/mutation only in debug mode (and 
hides the field in production). That could be useful, right?

First, we have to define the annotation. Annotations are handled by the great [doctrine/annotations](https://www.doctrine-project.org/projects/doctrine-annotations/en/1.6/index.html) library.

**OnlyDebug.php**
```php
namespace App\Annotations;

use TheCodingMachine\GraphQLite\Annotations\MiddlewareAnnotationInterface;

/**
 * @Annotation
 * @Target({"METHOD", "ANNOTATION"})
 */
class OnlyDebug implements MiddlewareAnnotationInterface
{
}
```

Apart from being a classical annotation, this class implements the `MiddlewareAnnotationInterface`. This interface
is a "marker" interface. It does not have any methods. It is just used to tell GraphQLite that this annotation 
is to be used by middlewares.

Now, we can write a middleware that will act upon this annotation.

```php
namespace App\Middlewares;

use App\Annotations\OnlyDebug;
use TheCodingMachine\GraphQLite\Middlewares\FieldMiddlewareInterface;
use GraphQL\Type\Definition\FieldDefinition;
use TheCodingMachine\GraphQLite\QueryFieldDescriptor;

/**
 * Middleware in charge of hiding a field if it is annotated with @OnlyDebug and the DEBUG constant is not set
 */
class OnlyDebugFieldMiddleware implements FieldMiddlewareInterface
{
    public function process(QueryFieldDescriptor $queryFieldDescriptor, FieldHandlerInterface $fieldHandler): ?FieldDefinition
    {
        $annotations = $queryFieldDescriptor->getMiddlewareAnnotations();

        /**
         * @var OnlyDebug $onlyDebug
         */
        $onlyDebug = $annotations->getAnnotationByType(OnlyDebug::class);

        if ($onlyDebug !== null && !DEBUG) {
            // If the onlyDebug annotation is present, returns null.
            // Returning null will hide the field.
            return null;
        }

        // Otherwise, let's continue the middleware pipe without touching anything.
        return $fieldHandler->handle($queryFieldDescriptor);
    }
}
```

The final thing we have to do is to register the middleware.

- Assuming you are using the `SchemaFactory` to initialize GraphQLite, you can register the field middleware using:
  ```php
  $schemaFactory->addFieldMiddleware(new OnlyDebugFieldMiddleware());
  ```
- If you are using the Symfony bundle, you can register your field middleware services by tagging them with the `graphql.field_middleware` tag.
