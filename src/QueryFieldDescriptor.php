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
use TheCodingMachine\GraphQLite\Middlewares\SourceMethodResolver;
use TheCodingMachine\GraphQLite\Middlewares\SourcePropertyResolver;
use TheCodingMachine\GraphQLite\Parameters\ParameterInterface;
use TheCodingMachine\GraphQLite\Utils\Cloneable;

use function assert;
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
     * @param array<string, ParameterInterface> $parameters
     * @param callable $callable
     * @param bool $injectSource Whether we should inject the source as the first parameter or not.
     */
    public function __construct(
        private readonly string $name,
        private readonly OutputType&Type $type,
        private readonly array $parameters = [],
        private readonly mixed $callable = null,
        private readonly string|null $targetClass = null,
        private readonly string|null $targetMethodOnSource = null,
        private readonly string|null $targetPropertyOnSource = null,
        private readonly string|null $magicProperty = null,
        private readonly bool $injectSource = false,
        private readonly string|null $comment = null,
        private readonly string|null $deprecationReason = null,
        private readonly MiddlewareAnnotations $middlewareAnnotations = new MiddlewareAnnotations([]),
        private readonly ReflectionMethod|null $refMethod = null,
        private readonly ReflectionProperty|null $refProperty = null,
    )
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function withName(string $name): self
    {
        return $this->with(name: $name);
    }

    public function getType(): OutputType&Type
    {
        return $this->type;
    }

    public function withType(OutputType&Type $type): self
    {
        return $this->with(type: $type);
    }

    /** @return array<string, ParameterInterface> */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /** @param array<string, ParameterInterface> $parameters */
    public function withParameters(array $parameters): self
    {
        return $this->with(parameters: $parameters);
    }

    /**
     * Sets the callable targeting the resolver function if the resolver function is part of a service.
     * This should not be used in the context of a field middleware.
     * Use getResolver/setResolver if you want to wrap the resolver in another method.
     */
    public function withCallable(callable $callable): self
    {
        if (isset($this->originalResolver)) {
            throw new GraphQLRuntimeException('You cannot modify the callable via withCallable because it was already used. You can still wrap the callable using getResolver/withResolver');
        }

        return $this->with(
            callable: $callable,
            targetClass: null,
            targetMethodOnSource: null,
            targetPropertyOnSource: null,
            magicProperty: null,
        );
    }

    public function withTargetMethodOnSource(string $className, string $targetMethodOnSource): self
    {
        if (isset($this->originalResolver)) {
            throw new GraphQLRuntimeException('You cannot modify the target method via withTargetMethodOnSource because it was already used. You can still wrap the callable using getResolver/withResolver');
        }

        return $this->with(
            callable: null,
            targetClass: $className,
            targetMethodOnSource: $targetMethodOnSource,
            targetPropertyOnSource: null,
            magicProperty: null,
        );
    }

    public function withTargetPropertyOnSource(string $className, string|null $targetPropertyOnSource): self
    {
        if (isset($this->originalResolver)) {
            throw new GraphQLRuntimeException('You cannot modify the target method via withTargetMethodOnSource because it was already used. You can still wrap the callable using getResolver/withResolver');
        }

        return $this->with(
            callable: null,
            targetClass: $className,
            targetMethodOnSource: null,
            targetPropertyOnSource: $targetPropertyOnSource,
            magicProperty: null,
        );
    }

    public function withMagicProperty(string $className, string $magicProperty): self
    {
        if (isset($this->originalResolver)) {
            throw new GraphQLRuntimeException('You cannot modify the target method via withMagicProperty because it was already used. You can still wrap the callable using getResolver/withResolver');
        }

        return $this->with(
            callable: null,
            targetClass: $className,
            targetMethodOnSource: null,
            targetPropertyOnSource: null,
            magicProperty: $magicProperty,
        );
    }

    public function isInjectSource(): bool
    {
        return $this->injectSource;
    }

    public function withInjectSource(bool $injectSource): self
    {
        return $this->with(injectSource: $injectSource);
    }

    public function getComment(): string|null
    {
        return $this->comment;
    }

    public function withComment(string|null $comment): self
    {
        return $this->with(comment: $comment);
    }

    public function getDeprecationReason(): string|null
    {
        return $this->deprecationReason;
    }

    public function withDeprecationReason(string|null $deprecationReason): self
    {
        return $this->with(deprecationReason: $deprecationReason);
    }

    public function getMiddlewareAnnotations(): MiddlewareAnnotations
    {
        return $this->middlewareAnnotations;
    }

    public function withMiddlewareAnnotations(MiddlewareAnnotations $middlewareAnnotations): self
    {
        return $this->with(middlewareAnnotations: $middlewareAnnotations);
    }

    public function getRefMethod(): ReflectionMethod|null
    {
        return $this->refMethod;
    }

    public function withRefMethod(ReflectionMethod $refMethod): self
    {
        return $this->with(refMethod: $refMethod);
    }

    public function getRefProperty(): ReflectionProperty|null
    {
        return $this->refProperty;
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
            assert($this->targetClass !== null);

            $this->originalResolver = new SourceMethodResolver($this->targetClass, $this->targetMethodOnSource);
        } elseif ($this->targetPropertyOnSource !== null) {
            assert($this->targetClass !== null);

            $this->originalResolver = new SourcePropertyResolver($this->targetClass, $this->targetPropertyOnSource);
        } elseif ($this->magicProperty !== null) {
            assert($this->targetClass !== null);

            $this->originalResolver = new MagicPropertyResolver($this->targetClass, $this->magicProperty);
        } else {
            throw new GraphQLRuntimeException('The QueryFieldDescriptor should be passed either a resolve method (via withCallable) or a target method on source object (via withTargetMethodOnSource) or a magic property (via withMagicProperty).');
        }

        return $this->originalResolver;
    }

    /**
     * Returns the callable that will be used to evaluate the field. This callable might have been modified to wrap
     * the original callable.
     */
    public function getResolver(): callable
    {
        if (! isset($this->resolver)) {
            $this->resolver = $this->getOriginalResolver();
        }

        return $this->resolver;
    }

    public function withResolver(callable $resolver): self
    {
        return $this->with(resolver: $resolver);
    }
}
