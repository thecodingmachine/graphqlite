---
id: argument-resolving
title: Extending argument resolving
sidebar_label: Custom argument resolving
---
<small>Available in GraphQLite 4.0+</small>

Using a **parameter middleware**, you can hook into the argument resolution of field/query/mutation/factory.

<div class="alert alert-info">Use a parameter middleware if you want to alter the way arguments are injected  in a method 
or if you want to alter the way input types are imported (for instance if you want to add a validation step)</div>

As an example, GraphQLite uses *parameter middlewares* internally to:

- Inject the Webonyx GraphQL resolution object when you type-hint on the `ResolveInfo` object. For instance:
  ```php
  /**
   * @Query
   * @return Product[]
   */
  public function products(ResolveInfo $info): array  
  ```
  In the query above, the `$info` argument is filled with the Webonyx `ResolveInfo` class thanks to the 
  [`ResolveInfoParameterHandler parameter middleware`](https://github.com/thecodingmachine/graphqlite/blob/master/src/Mappers/Parameters/ResolveInfoParameterHandler.php)
- Inject a service from the container when you use the `@Autowire` annotation
- Perform validation with the `@Validate` annotation (in Laravel package)

<!-- https://docs.google.com/drawings/d/10zHfWdbvEab6_dyQBcM68_I_bXQkZtO5ePqt4jdDlk8/edit?usp=sharing -->

**Parameter middlewares**

![](assets/parameter_middleware.svg)

Each middleware is passed number of objects describing the parameter:

- a PHP `ReflectionParameter` object representing the parameter being manipulated
- a `phpDocumentor\Reflection\DocBlock` instance (useful to analyze the `@param` comment if any)
- a `phpDocumentor\Reflection\Type` instance (useful to analyze the type if the argument)
- a `TheCodingMachine\GraphQLite\Annotations\ParameterAnnotations` instance. This is a collection of all custom annotations that apply to this specific argument (more on that later)
- a `$next` handler to pass the argument resolving to the next middleware.

Parameter resolution is done in 2 passes.

On the first pass, middlewares are traversed. They must return a `TheCodingMachine\GraphQLite\Parameters\ParameterInterface` (an object that does the actual resolving).

```php
interface ParameterMiddlewareInterface
{
    public function mapParameter(ReflectionParameter $parameter, DocBlock $docBlock, ?Type $paramTagType, ParameterAnnotations $parameterAnnotations, ParameterHandlerInterface $next): ParameterInterface;
}
```

Then, resolution actually happen by executing the resolver (this is the second pass).

## Annotations parsing

If you plan to use annotations while resolving arguments, your annotation should extend the [`ParameterAnnotationInterface`](https://github.com/thecodingmachine/graphqlite/blob/master/src/Annotations/ParameterAnnotationInterface.php)

For instance, if we want GraphQLite to inject a service in an argument, we can use `@Autowire(for="myService")`.

The annotation looks like this:

```php
/**
 * Use this annotation to autowire a service from the container into a given parameter of a field/query/mutation.
 *
 * @Annotation
 */
class Autowire implements ParameterAnnotationInterface
{
    /**
     * @var string
     */
    public $for;

    /**
     * The getTarget method must return the name of the argument
     */
    public function getTarget(): string
    {
        return $this->for;
    }
}
```

## Writing the parameter middleware

The middleware purpose is to analyze a parameter and decide whether or not it can handle it.

**Parameter middleware class**
```php
class ContainerParameterHandler implements ParameterMiddlewareInterface
{
    /** @var ContainerInterface */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function mapParameter(ReflectionParameter $parameter, DocBlock $docBlock, ?Type $paramTagType, ParameterAnnotations $parameterAnnotations, ParameterHandlerInterface $next): ParameterInterface
    {
        // The $parameterAnnotations object can be used to fetch any annotation implementing ParameterAnnotationInterface
        $autowire = $parameterAnnotations->getAnnotationByType(Autowire::class);

        if ($autowire === null) {
            // If there are no annotation, this middleware cannot handle the parameter. Let's ask
            // the next middleware in the chain (using the $next object)
            return $next->mapParameter($parameter, $docBlock, $paramTagType, $parameterAnnotations);
        }

        // We found a @Autowire annotation, let's return a parameter resolver.
        return new ContainerParameter($this->container, $parameter->getType());
    }
}
```

The last step is to write the actual parameter resolver.

**Parameter resolver class**
```php
/**
 * A parameter filled from the container.
 */
class ContainerParameter implements ParameterInterface
{
    /** @var ContainerInterface */
    private $container;
    /** @var string */
    private $identifier;

    public function __construct(ContainerInterface $container, string $identifier)
    {
        $this->container = $container;
        $this->identifier = $identifier;
    }

    /**
     * The "resolver" returns the actual value that will be fed to the function.
     */
    public function resolve(?object $source, array $args, $context, ResolveInfo $info)
    {
        return $this->container->get($this->identifier);
    }
}
```

## Registering a parameter middleware

The last step is to register the parameter middleware we just wrote:

You can register your own parameter middlewares using the `SchemaFactory::addParameterMiddleware()` method.

```php
$schemaFactory->addParameterMiddleware(new ContainerParameterHandler($container));
```
 
If you are using the Symfony bundle, you can tag the service as "graphql.parameter_middleware".
