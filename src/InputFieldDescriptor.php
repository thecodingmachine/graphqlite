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
use TheCodingMachine\GraphQLite\Middlewares\SourceResolver;
use TheCodingMachine\GraphQLite\Parameters\ParameterInterface;

use function is_callable;

/**
 * A class that describes a field to be created.
 * To contains getters and setters to alter the field behaviour.
 * It is meant to be passed from middleware to middleware.
 */
class InputFieldDescriptor
{
    private string $name;
    /** @var InputType|(NullableType&Type) */
    private $type;
    /** @var array<string, ParameterInterface> */
    private array $parameters = [];
    /** @var callable|null */
    private $callable;
    private ?string $targetMethodOnSource;
    private ?string $targetPropertyOnSource;

    /**
     * Implement in future PR
     */
    // private ?string $magicProperty;

    /**
     * Whether we should inject the source as the first parameter or not.
     */
    private bool $injectSource = false;
    private ?string $comment;
    private MiddlewareAnnotations $middlewareAnnotations;
    private ReflectionMethod $refMethod;
    private ReflectionProperty $refProperty;
    private ?ResolverInterface $originalResolver;
    /** @var callable */
    private $resolver;
    private bool $isUpdate = false;
    private bool $hasDefaultValue = false;
    private mixed $defaultValue;

    public function isUpdate(): bool
    {
        return $this->isUpdate;
    }

    public function setIsUpdate(bool $isUpdate): void
    {
        $this->isUpdate = $isUpdate;
    }

    public function hasDefaultValue(): bool
    {
        return $this->hasDefaultValue;
    }

    public function setHasDefaultValue(bool $hasDefaultValue): void
    {
        $this->hasDefaultValue = $hasDefaultValue;
    }

    public function getDefaultValue(): mixed
    {
        return $this->defaultValue;
    }

    public function setDefaultValue(mixed $defaultValue): void
    {
        $this->defaultValue = $defaultValue;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return InputType|(NullableType&Type)
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param InputType|(NullableType&Type) $type
     */
    public function setType($type): void
    {
        $this->type = $type;
    }

    /**
     * @return array<string, ParameterInterface>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @param array<string, ParameterInterface> $parameters
     */
    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }

    /**
     * Sets the callable targeting the resolver function if the resolver function is part of a service.
     * This should not be used in the context of a field middleware.
     * Use getResolver/setResolver if you want to wrap the resolver in another method.
     */
    public function setCallable(callable $callable): void
    {
        if ($this->originalResolver !== null) {
            throw new GraphQLRuntimeException('You cannot modify the callable via setCallable because it was already used. You can still wrap the callable using getResolver/setResolver');
        }

        $this->callable = $callable;
        $this->targetMethodOnSource = null;
        $this->targetPropertyOnSource = null;

        // To be enabled in a future PR
        // $this->magicProperty = null;
    }

    public function setTargetMethodOnSource(string $targetMethodOnSource): void
    {
        if ($this->originalResolver !== null) {
            throw new GraphQLRuntimeException('You cannot modify the target method via setTargetMethodOnSource because it was already used. You can still wrap the callable using getResolver/setResolver');
        }

        $this->callable = null;
        $this->targetMethodOnSource = $targetMethodOnSource;
        $this->targetPropertyOnSource = null;

        // To be enabled in a future PR
        // $this->magicProperty = null;
    }

    public function setTargetPropertyOnSource(?string $targetPropertyOnSource): void
    {
        if ($this->originalResolver !== null) {
            throw new GraphQLRuntimeException('You cannot modify the target method via setTargetMethodOnSource because it was already used. You can still wrap the callable using getResolver/setResolver');
        }

        $this->callable = null;
        $this->targetMethodOnSource = null;
        $this->targetPropertyOnSource = $targetPropertyOnSource;

        // To be enabled in a future PR
        // $this->magicProperty = null;
    }

    public function isInjectSource(): bool
    {
        return $this->injectSource;
    }

    public function setInjectSource(bool $injectSource): void
    {
        $this->injectSource = $injectSource;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): void
    {
        $this->comment = $comment;
    }

    public function getMiddlewareAnnotations(): MiddlewareAnnotations
    {
        return $this->middlewareAnnotations;
    }

    public function setMiddlewareAnnotations(MiddlewareAnnotations $middlewareAnnotations): void
    {
        $this->middlewareAnnotations = $middlewareAnnotations;
    }

    public function getRefMethod(): ReflectionMethod
    {
        return $this->refMethod;
    }

    public function setRefMethod(ReflectionMethod $refMethod): void
    {
        $this->refMethod = $refMethod;
    }

    public function getRefProperty(): ReflectionProperty
    {
        return $this->refProperty;
    }

    public function setRefProperty(ReflectionProperty $refProperty): void
    {
        $this->refProperty = $refProperty;
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
            $this->originalResolver = new SourceResolver($this->targetMethodOnSource);
        } elseif ($this->targetPropertyOnSource !== null) {
            $this->originalResolver = new SourceInputPropertyResolver($this->targetPropertyOnSource);
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
        if ($this->resolver === null) {
            $this->resolver = $this->getOriginalResolver();
        }

        return $this->resolver;
    }

    public function setResolver(callable $resolver): void
    {
        $this->resolver = $resolver;
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
    * $this->callable = null;
    * $this->targetMethodOnSource = null;
    * $this->targetPropertyOnSource = null;
    * $this->magicProperty = $magicProperty;
    * }
    */
}
