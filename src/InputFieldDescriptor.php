<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use GraphQL\Type\Definition\InputType;
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

use function assert;
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
     * @param array<string, ParameterInterface> $parameters
     * @param callable|null $callable
     * @param bool $injectSource Whether we should inject the source as the first parameter or not.
     */
    public function __construct(
        private readonly string $name,
        private readonly InputType&Type $type,
        private readonly array $parameters = [],
        private readonly mixed $callable = null,
        private readonly string|null $targetClass = null,
        private readonly string|null $targetMethodOnSource = null,
        private readonly string|null $targetPropertyOnSource = null,
        private readonly bool $injectSource = false,
        private readonly string|null $comment = null,
        private readonly MiddlewareAnnotations $middlewareAnnotations = new MiddlewareAnnotations([]),
        private readonly ReflectionMethod|null $refMethod = null,
        private readonly ReflectionProperty|null $refProperty = null,
        private readonly bool $isUpdate = false,
        private readonly bool $hasDefaultValue = false,
        private readonly mixed $defaultValue = null,
    )
    {
    }

    public function isUpdate(): bool
    {
        return $this->isUpdate;
    }

    public function withIsUpdate(bool $isUpdate): self
    {
        return $this->with(isUpdate: $isUpdate);
    }

    public function hasDefaultValue(): bool
    {
        return $this->hasDefaultValue;
    }

    public function withHasDefaultValue(bool $hasDefaultValue): self
    {
        return $this->with(hasDefaultValue: $hasDefaultValue);
    }

    public function getDefaultValue(): mixed
    {
        return $this->defaultValue;
    }

    public function withDefaultValue(mixed $defaultValue): self
    {
        return $this->with(defaultValue: $defaultValue);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function withName(string $name): self
    {
        return $this->with(name: $name);
    }

    public function getType(): InputType&Type
    {
        return $this->type;
    }

    public function withType(InputType&Type $type): self
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
            throw new GraphQLRuntimeException('You cannot modify the target method via withCallable because it was already used. You can still wrap the callable using getResolver/withResolver');
        }

        // To be enabled in a future PR
        // $this->magicProperty = null;
        return $this->with(
            callable: $callable,
            targetClass: null,
            targetMethodOnSource: null,
            targetPropertyOnSource: null,
        );
    }

    public function withTargetMethodOnSource(string $className, string $targetMethodOnSource): self
    {
        if (isset($this->originalResolver)) {
            throw new GraphQLRuntimeException('You cannot modify the target method via withTargetMethodOnSource because it was already used. You can still wrap the callable using getResolver/withResolver');
        }

        // To be enabled in a future PR
        // $this->magicProperty = null;
        return $this->with(
            callable: null,
            targetClass: $className,
            targetMethodOnSource: $targetMethodOnSource,
            targetPropertyOnSource: null,
        );
    }

    public function withTargetPropertyOnSource(string $className, string|null $targetPropertyOnSource): self
    {
        if (isset($this->originalResolver)) {
            throw new GraphQLRuntimeException('You cannot modify the target method via withTargetMethodOnSource because it was already used. You can still wrap the callable using getResolver/withResolver');
        }

        // To be enabled in a future PR
        // $this->magicProperty = null;
        return $this->with(
            callable: null,
            targetClass: $className,
            targetMethodOnSource: null,
            targetPropertyOnSource: $targetPropertyOnSource,
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

        if (is_callable($this->callable)) {
            /** @var callable&array{0:object, 1:string} $callable */
            $callable = $this->callable;
            $this->originalResolver = new ServiceResolver($callable);
        } elseif ($this->targetMethodOnSource !== null) {
            assert($this->targetClass !== null);

            $this->originalResolver = new SourceMethodResolver($this->targetClass, $this->targetMethodOnSource);
        } elseif ($this->targetPropertyOnSource !== null) {
            assert($this->targetClass !== null);

            $this->originalResolver = new SourceInputPropertyResolver($this->targetClass, $this->targetPropertyOnSource);
            // } elseif ($this->magicProperty !== null) {
            // Enable magic properties in a future PR
            // $this->originalResolver = new MagicInputPropertyResolver($this->magicProperty);
        } else {
            throw new GraphQLRuntimeException('The InputFieldDescriptor should be passed either a resolve method (via withCallable) or a target method on source object (via withTargetMethodOnSource).');
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

    /*
    * Set the magic property
    *
    * @todo enable this in a future PR
    *
    * public function setMagicProperty(string $magicProperty): void
    * {
    * if ($this->originalResolver !== null) {
    * throw new GraphQLRuntimeException('You cannot modify the target method via withMagicProperty because it was already used. You can still wrap the callable using getResolver/withResolver');
    * }
    * $this->targetMethodOnSource = null;
    * $this->targetPropertyOnSource = null;
    * return $this->with(magicProperty: $magicProperty);
    * }
    */
}
