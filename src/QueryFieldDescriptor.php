<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\Type;
use ReflectionMethod;
use ReflectionProperty;
use TheCodingMachine\GraphQLite\Annotations\MiddlewareAnnotations;
use TheCodingMachine\GraphQLite\Middlewares\MagicPropertyResolver;
use TheCodingMachine\GraphQLite\Middlewares\ResolverInterface;
use TheCodingMachine\GraphQLite\Middlewares\ServiceResolver;
use TheCodingMachine\GraphQLite\Middlewares\SourcePropertyResolver;
use TheCodingMachine\GraphQLite\Middlewares\SourceMethodResolver;
use TheCodingMachine\GraphQLite\Parameters\ParameterInterface;

use TheCodingMachine\GraphQLite\Utils\Cloneable;
use function is_array;

/**
 * A class that describes a field to be created.
 * To contains getters and setters to alter the field behaviour.
 * It is meant to be passed from middleware to middleware.
 */
class QueryFieldDescriptor
{
    use Cloneable;

    private readonly ResolverInterface $originalResolver;
    /** @var callable */
    private readonly mixed $resolver;

    /**
     * @param (OutputType&Type)|null $type
     * @param array<string, ParameterInterface> $parameters
     * @param array<string, ParameterInterface> $prefetchParameters
     * @param callable $callable
     * @param bool $injectSource Whether we should inject the source as the first parameter or not.
     */
    public function __construct(
        public readonly string|null             $name = null,
        public readonly Type|null               $type = null,
        public readonly array                   $parameters = [],
        public readonly array                   $prefetchParameters = [],
        public readonly string|null             $prefetchMethodName = null,
        private readonly mixed $callable = null,
        private readonly string|null            $targetMethodOnSource = null,
        private readonly string|null            $targetPropertyOnSource = null,
        private readonly string|null            $magicProperty = null,
        public readonly bool                    $injectSource = false,
        public readonly string|null             $comment = null,
        public readonly string|null             $deprecationReason = null,
        public readonly MiddlewareAnnotations   $middlewareAnnotations = new MiddlewareAnnotations([]),
        public readonly ReflectionMethod|null   $refMethod = null,
        public readonly ReflectionProperty|null $refProperty = null,
    )
    {
    }

    public function withName(string $name): self
    {
        return $this->with(name: $name);
    }

    public function withType(OutputType&Type $type): self
    {
        return $this->with(type: $type);
    }

    /** @param array<string, ParameterInterface> $parameters */
    public function withParameters(array $parameters): self
    {
        return $this->with(parameters: $parameters);
    }

    /** @param array<string, ParameterInterface> $prefetchParameters */
    public function withPrefetchParameters(array $prefetchParameters): self
    {
        return $this->with(prefetchParameters: $prefetchParameters);
    }

    public function withPrefetchMethodName(string|null $prefetchMethodName): self
    {
        return $this->with(prefetchMethodName: $prefetchMethodName);
    }

    /**
     * Sets the callable targeting the resolver function if the resolver function is part of a service.
     * This should not be used in the context of a field middleware.
     * Use getResolver/setResolver if you want to wrap the resolver in another method.
     */
    public function withCallable(callable $callable): self
    {
        if (isset($this->originalResolver)) {
            throw new GraphQLRuntimeException('You cannot modify the callable via setCallable because it was already used. You can still wrap the callable using getResolver/setResolver');
        }

        return $this->with(
            callable: $callable,
            targetMethodOnSource: null,
            targetPropertyOnSource: null,
            magicProperty: null,
        );
    }

    public function withTargetMethodOnSource(string $targetMethodOnSource): self
    {
        if (isset($this->originalResolver)) {
            throw new GraphQLRuntimeException('You cannot modify the target method via setTargetMethodOnSource because it was already used. You can still wrap the callable using getResolver/setResolver');
        }

        return $this->with(
            callable: null,
            targetMethodOnSource: $targetMethodOnSource,
            targetPropertyOnSource: null,
            magicProperty: null,
        );
    }

    public function withTargetPropertyOnSource(string|null $targetPropertyOnSource): self
    {
        if (isset($this->originalResolver)) {
            throw new GraphQLRuntimeException('You cannot modify the target method via setTargetMethodOnSource because it was already used. You can still wrap the callable using getResolver/setResolver');
        }

        return $this->with(
            callable: null,
            targetMethodOnSource: null,
            targetPropertyOnSource: $targetPropertyOnSource,
            magicProperty: null,
        );
    }

    public function withMagicProperty(string $magicProperty): self
    {
        if (isset($this->originalResolver)) {
            throw new GraphQLRuntimeException('You cannot modify the target method via setMagicProperty because it was already used. You can still wrap the callable using getResolver/setResolver');
        }

        return $this->with(
            callable: null,
            targetMethodOnSource: null,
            targetPropertyOnSource: null,
            magicProperty: $magicProperty,
        );
    }

    public function withInjectSource(bool $injectSource): self
    {
        return $this->with(injectSource: $injectSource);
    }

    public function withComment(string|null $comment): self
    {
        return $this->with(comment: $comment);
    }

    public function withDeprecationReason(string|null $deprecationReason): self
    {
        return $this->with(deprecationReason: $deprecationReason);
    }

    public function withMiddlewareAnnotations(MiddlewareAnnotations $middlewareAnnotations): self
    {
        return $this->with(middlewareAnnotations: $middlewareAnnotations);
    }

    public function withRefMethod(ReflectionMethod $refMethod): self
    {
        return $this->with(refMethod: $refMethod);
    }

    public function withRefProperty(ReflectionProperty $refProperty): self
    {
        return $this->with(refProperty: $refProperty);
    }

    /**
     * Returns the original callable that will be used to resolve the field.
     */
    public function getOriginalResolver(): ResolverInterface
    {
        if (isset($this->originalResolver)) {
            return $this->originalResolver;
        }

        if (is_array($this->callable)) {
            /** @var callable&array{0:object, 1:string} $callable */
            $callable = $this->callable;
            $this->originalResolver = new ServiceResolver($callable);
        } elseif ($this->targetMethodOnSource !== null) {
            $this->originalResolver = new SourceMethodResolver('test', $this->targetMethodOnSource);
        } elseif ($this->targetPropertyOnSource !== null) {
            $this->originalResolver = new SourcePropertyResolver('test', $this->targetPropertyOnSource);
        } elseif ($this->magicProperty !== null) {
            $this->originalResolver = new MagicPropertyResolver('test', $this->magicProperty);
        } else {
            throw new GraphQLRuntimeException('The QueryFieldDescriptor should be passed either a resolve method (via setCallable) or a target method on source object (via setTargetMethodOnSource) or a magic property (via setMagicProperty).');
        }

        return $this->originalResolver;
    }

    /**
     * Returns the callable that will be used to evaluate the field. This callable might have been modified to wrap
     * the original callable.
     */
    public function getResolver(): callable
    {
        if (!isset($this->resolver)) {
            $this->resolver = $this->getOriginalResolver();
        }

        return $this->resolver;
    }

    public function withResolver(callable $resolver): self
    {
        return $this->with(resolver: $resolver);
    }
}
