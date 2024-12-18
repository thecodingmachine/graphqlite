---
id: field-middlewares
title: Adding custom attributes with Field middlewares
sidebar_label: Custom attributes
---

<small>Available in GraphQLite 4.0+</small>

Just like the `#[Logged]` or `#[Right]` attribute, you can develop your own attribute that extends/modifies the behaviour of a field/query/mutation.

<div class="alert alert--warning">
    If you want to create an attribute that targets a single argument (like <code>#[AutoWire]</code>), you should rather check the documentation about <a href="argument-resolving">custom argument resolving</a>
</div>

## Field middlewares

GraphQLite is based on the Webonyx/Graphql-PHP library. In Webonyx, fields are represented by the `FieldDefinition` class.
In order to create a `FieldDefinition` instance for your field, GraphQLite goes through a series of "middlewares".

![](/img/field_middleware.svg)

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
    public function withName(string $name): self  { /* ... */ }
    public function getType() { /* ... */ }
    public function withType($type): self  { /* ... */ }
    public function getParameters(): array  { /* ... */ }
    public function withParameters(array $parameters): self  { /* ... */ }
    public function withCallable(callable $callable): self { /* ... */ }
    public function withTargetMethodOnSource(?string $targetMethodOnSource): self { /* ... */ }
    public function isInjectSource(): bool { /* ... */ }
    public function withInjectSource(bool $injectSource): self { /* ... */ }
    public function getComment(): ?string { /* ... */ }
    public function withComment(?string $comment): self { /* ... */ }
    public function getMiddlewareAnnotations(): MiddlewareAnnotations { /* ... */ }
    public function withMiddlewareAnnotations(MiddlewareAnnotations $middlewareAnnotations): self { /* ... */ }
    public function getOriginalResolver(): ResolverInterface { /* ... */ }
    public function getResolver(): callable { /* ... */ }
    public function withResolver(callable $resolver): self { /* ... */ }
}
```

The role of a middleware is to analyze the `QueryFieldDescriptor` and modify it (or to directly return a `FieldDefinition`).

If you want the field to purely disappear, your middleware can return `null`, although this should be used with caution:
field middlewares only get called once per Schema instance. If you use a long-running server (like Laravel Octane, Swoole, RoadRunner etc)
and share the same Schema instance across requests, you will not be able to hide fields based on request data.

## Attributes parsing

Take a look at the `QueryFieldDescriptor::getMiddlewareAnnotations()`.

It returns the list of attributes applied to your field that implements the `MiddlewareAnnotationInterface`.

Let's imagine you want to add a `#[OnlyDebug]` attribute that displays a field/query/mutation only in debug mode (and
hides the field in production). That could be useful, right?

First, we have to define the attribute.

```php title="OnlyDebug.php"
namespace App\Annotations;

use Attribute;
use TheCodingMachine\GraphQLite\Annotations\MiddlewareAnnotationInterface;

#[Attribute(Attribute::TARGET_METHOD)]
class OnlyDebug implements MiddlewareAnnotationInterface
{
}
```

Apart from being a classical attribute, this class implements the `MiddlewareAnnotationInterface`. This interface is a "marker" interface. It does not have any methods. It is just used to tell GraphQLite that this attribute is to be used by middlewares.

Now, we can write a middleware that will act upon this attribute.

```php
namespace App\Middlewares;

use App\Annotations\OnlyDebug;
use TheCodingMachine\GraphQLite\Middlewares\FieldMiddlewareInterface;
use GraphQL\Type\Definition\FieldDefinition;
use TheCodingMachine\GraphQLite\QueryFieldDescriptor;

/**
 * Middleware in charge of hiding a field if it is annotated with #[OnlyDebug] and the DEBUG constant is not set
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
            // If the onlyDebug attribute is present, returns null.
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
