<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\Type;
use InvalidArgumentException;
use ReflectionMethod;
use TheCodingMachine\GraphQLite\Annotations\MiddlewareAnnotations;
use TheCodingMachine\GraphQLite\Middlewares\MagicPropertyResolver;
use TheCodingMachine\GraphQLite\Middlewares\ResolverInterface;
use TheCodingMachine\GraphQLite\Middlewares\ServiceResolver;
use TheCodingMachine\GraphQLite\Middlewares\SourceResolver;
use TheCodingMachine\GraphQLite\Parameters\ParameterInterface;

/**
 * A class that describes a field to be created.
 * To contains getters and setters to alter the field behaviour.
 * It is meant to be passed from middleware to middleware.
 */
class QueryFieldDescriptor
{
    /** @var string */
    private $name;
    /** @var OutputType&Type */
    private $type;
    /** @var array<string, ParameterInterface> */
    private $parameters = [];
    /** @var array<string, ParameterInterface> */
    private $prefetchParameters = [];
    /** @var string|null */
    private $prefetchMethodName;
    /** @var (callable&array{0:object, 1:string})|null */
    private $callable;
    /** @var string|null */
    private $targetMethodOnSource;
    /** @var string|null */
    private $magicProperty;
    /**
     * Whether we should inject the source as the first parameter or not.
     *
     * @var bool
     */
    private $injectSource;
    /** @var string|null */
    private $comment;
    /** @var MiddlewareAnnotations */
    private $middlewareAnnotations;
    /** @var ReflectionMethod */
    private $refMethod;
    /** @var ResolverInterface */
    private $originalResolver;
    /** @var callable */
    private $resolver;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return OutputType&Type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param OutputType&Type $type
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
     * @return array<string, ParameterInterface>
     */
    public function getPrefetchParameters(): array
    {
        return $this->prefetchParameters;
    }

    /**
     * @param array<string, ParameterInterface> $prefetchParameters
     */
    public function setPrefetchParameters(array $prefetchParameters): void
    {
        $this->prefetchParameters = $prefetchParameters;
    }

    public function getPrefetchMethodName(): ?string
    {
        return $this->prefetchMethodName;
    }

    public function setPrefetchMethodName(?string $prefetchMethodName): void
    {
        $this->prefetchMethodName = $prefetchMethodName;
    }

    /**
     * Sets the callable targeting the resolver function if the resolver function is part of a service.
     * This should not be used in the context of a field middleware.
     * Use getResolver/setResolver if you want to wrap the resolver in another method.
     *
     * @param callable&array{0:object, 1:string}  $callable
     */
    public function setCallable(callable $callable): void
    {
        if ($this->originalResolver !== null) {
            throw new GraphQLRuntimeException('You cannot modify the callable via setCallable because it was already used. You can still wrap the callable using getResolver/setResolver');
        }
        $this->callable = $callable;
        $this->targetMethodOnSource = null;
        $this->magicProperty = null;
    }

    public function setTargetMethodOnSource(string $targetMethodOnSource): void
    {
        if ($this->originalResolver !== null) {
            throw new GraphQLRuntimeException('You cannot modify the target method via setTargetMethodOnSource because it was already used. You can still wrap the callable using getResolver/setResolver');
        }
        $this->callable = null;
        $this->targetMethodOnSource = $targetMethodOnSource;
        $this->magicProperty = null;
    }

    public function setMagicProperty(string $magicProperty): void
    {
        if ($this->originalResolver !== null) {
            throw new GraphQLRuntimeException('You cannot modify the target method via setMagicProperty because it was already used. You can still wrap the callable using getResolver/setResolver');
        }
        $this->callable = null;
        $this->targetMethodOnSource = null;
        $this->magicProperty = $magicProperty;
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

    /**
     * Returns the original callable that will be used to resolve the field.
     */
    public function getOriginalResolver(): ResolverInterface
    {
        if (isset($this->originalResolver)) {
            return $this->originalResolver;
        }

        if ($this->callable !== null) {
            $this->originalResolver = new ServiceResolver($this->callable);
        } elseif ($this->targetMethodOnSource !== null) {
            $this->originalResolver = new SourceResolver($this->targetMethodOnSource);
        } elseif ($this->magicProperty !== null) {
            $this->originalResolver = new MagicPropertyResolver($this->magicProperty);
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
        if ($this->resolver === null) {
            $this->resolver = $this->getOriginalResolver();
        }

        return $this->resolver;
    }

    public function setResolver(callable $resolver): void
    {
        $this->resolver = $resolver;
    }
}
