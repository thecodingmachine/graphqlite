<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\NullableType;
use GraphQL\Type\Definition\Type;
use ReflectionMethod;
use ReflectionProperty;
use TheCodingMachine\GraphQLite\Annotations\MiddlewareAnnotations;
use TheCodingMachine\GraphQLite\Middlewares\ResolverInterface;
use TheCodingMachine\GraphQLite\Middlewares\ServiceResolver;
use TheCodingMachine\GraphQLite\Middlewares\SourceInputPropertyResolver;
use TheCodingMachine\GraphQLite\Middlewares\SourceMethodResolver;
use TheCodingMachine\GraphQLite\Parameters\ParameterInterface;

use TheCodingMachine\GraphQLite\Utils\Cloneable;
use function is_callable;

/**
 * A class that describes a field to be created.
 * To contains getters and setters to alter the field behaviour.
 * It is meant to be passed from middleware to middleware.
 */
class InputFieldDescriptor
{
    use Cloneable;

    private readonly ResolverInterface $originalResolver;
    /** @var callable */
    private readonly mixed $resolver;

    /**
     * @param (InputType&Type)|(InputType&Type&NullableType)|null $type
     * @param array<string, ParameterInterface> $parameters
     * @param callable|null $callable
     * @param bool $injectSource Whether we should inject the source as the first parameter or not.
     */
    public function __construct(
        public readonly string|null $name = null,
        public readonly Type|null $type = null,
        public readonly array $parameters = [],
        public readonly mixed $callable = null,
        public readonly string|null $targetMethodOnSource = null,
        public readonly string|null $targetPropertyOnSource = null,
        public readonly bool $injectSource = false,
        public readonly string|null $comment = null,
        public readonly MiddlewareAnnotations $middlewareAnnotations = new MiddlewareAnnotations([]),
        public readonly ReflectionMethod|null $refMethod = null,
        public readonly ReflectionProperty|null $refProperty = null,
        public readonly bool $isUpdate = false,
        public readonly bool $hasDefaultValue = false,
        public readonly mixed $defaultValue = null,
    )
    {
    }

    public function withIsUpdate(bool $isUpdate): self
    {
        return $this->with(isUpdate: $isUpdate);
    }

    public function withHasDefaultValue(bool $hasDefaultValue): self
    {
        return $this->with(hasDefaultValue: $hasDefaultValue);
    }

    public function withDefaultValue(mixed $defaultValue): self
    {
        return $this->with(defaultValue: $defaultValue);
    }

    public function withName(string $name): self
    {
        return $this->with(name: $name);
    }

    public function withType(InputType&Type $type): self
    {
        return $this->with(type: $type);
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
            throw new GraphQLRuntimeException('You cannot modify the target method via setCallable because it was already used. You can still wrap the callable using getResolver/setResolver');
        }

        // To be enabled in a future PR
        // $this->magicProperty = null;
        return $this->with(
            callable: $callable,
            targetMethodOnSource: null,
            targetPropertyOnSource: null,
        );
    }

    public function withTargetMethodOnSource(string $targetMethodOnSource): self
    {
        if (isset($this->originalResolver)) {
            throw new GraphQLRuntimeException('You cannot modify the target method via setTargetMethodOnSource because it was already used. You can still wrap the callable using getResolver/setResolver');
        }

        // To be enabled in a future PR
        // $this->magicProperty = null;
        return $this->with(
            callable: null,
            targetMethodOnSource: $targetMethodOnSource,
            targetPropertyOnSource: null,
        );
    }

    public function withTargetPropertyOnSource(string|null $targetPropertyOnSource): self
    {
        if (isset($this->originalResolver)) {
            throw new GraphQLRuntimeException('You cannot modify the target method via setTargetMethodOnSource because it was already used. You can still wrap the callable using getResolver/setResolver');
        }

        // To be enabled in a future PR
        // $this->magicProperty = null;
        return $this->with(
            callable: null,
            targetMethodOnSource: null,
            targetPropertyOnSource: $targetPropertyOnSource,
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

        if (is_callable($this->callable)) {
            /** @var callable&array{0:object, 1:string} $callable */
            $callable = $this->callable;
            $this->originalResolver = new ServiceResolver($callable);
        } elseif ($this->targetMethodOnSource !== null) {
            $this->originalResolver = new SourceMethodResolver('test', $this->targetMethodOnSource);
        } elseif ($this->targetPropertyOnSource !== null) {
            $this->originalResolver = new SourceInputPropertyResolver('test', $this->targetPropertyOnSource);
            // } elseif ($this->magicProperty !== null) {
            // Enable magic properties in a future PR
            // $this->originalResolver = new MagicInputPropertyResolver($this->magicProperty);
        } else {
            throw new GraphQLRuntimeException('The InputFieldDescriptor should be passed either a resolve method (via setCallable) or a target method on source object (via setTargetMethodOnSource).');
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

    /*
    * Set the magic property
    *
    * @todo enable this in a future PR
    *
    * public function setMagicProperty(string $magicProperty): void
    * {
    * if ($this->originalResolver !== null) {
    * throw new GraphQLRuntimeException('You cannot modify the target method via setMagicProperty because it was already used. You can still wrap the callable using getResolver/setResolver');
    * }
    * $this->targetMethodOnSource = null;
    * $this->targetPropertyOnSource = null;
    * return $this->with(magicProperty: $magicProperty);
    * }
    */
}
