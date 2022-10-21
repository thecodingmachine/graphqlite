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
use TheCodingMachine\GraphQLite\Middlewares\SourceResolver;
use TheCodingMachine\GraphQLite\Parameters\ParameterInterface;

use function is_array;

/**
 * A class that describes a field to be created.
 * To contains getters and setters to alter the field behaviour.
 * It is meant to be passed from middleware to middleware.
 */
class QueryFieldDescriptor
{
    private string $name;
    /** @var (OutputType&Type)|null */
    private Type|null $type  = null;
    /** @var array<string, ParameterInterface> */
    private array $parameters = [];
    /** @var array<string, ParameterInterface> */
    private array $prefetchParameters = [];
    private string|null $prefetchMethodName = null;
    /** @var callable|null */
    private $callable;
    private string|null $targetMethodOnSource = null;
    private string|null $targetPropertyOnSource = null;
    private string|null $magicProperty = null;
    /**
     * Whether we should inject the source as the first parameter or not.
     */
    private bool $injectSource;
    private string|null $comment = null;
    private string|null $deprecationReason = null;
    private MiddlewareAnnotations $middlewareAnnotations;
    private ReflectionMethod $refMethod;
    private ReflectionProperty $refProperty;
    private ResolverInterface|null $originalResolver = null;
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

    /** @return (OutputType&Type)|null */
    public function getType(): Type|null
    {
        return $this->type;
    }

    /** @param OutputType&Type $type */
    public function setType(Type $type): void
    {
        $this->type = $type;
    }

    /** @return array<string, ParameterInterface> */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /** @param array<string, ParameterInterface> $parameters */
    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }

    /** @return array<string, ParameterInterface> */
    public function getPrefetchParameters(): array
    {
        return $this->prefetchParameters;
    }

    /** @param array<string, ParameterInterface> $prefetchParameters */
    public function setPrefetchParameters(array $prefetchParameters): void
    {
        $this->prefetchParameters = $prefetchParameters;
    }

    public function getPrefetchMethodName(): string|null
    {
        return $this->prefetchMethodName;
    }

    public function setPrefetchMethodName(string|null $prefetchMethodName): void
    {
        $this->prefetchMethodName = $prefetchMethodName;
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
        $this->magicProperty = null;
    }

    public function setTargetMethodOnSource(string $targetMethodOnSource): void
    {
        if ($this->originalResolver !== null) {
            throw new GraphQLRuntimeException('You cannot modify the target method via setTargetMethodOnSource because it was already used. You can still wrap the callable using getResolver/setResolver');
        }
        $this->callable = null;
        $this->targetMethodOnSource = $targetMethodOnSource;
        $this->targetPropertyOnSource = null;
        $this->magicProperty = null;
    }

    public function setTargetPropertyOnSource(string|null $targetPropertyOnSource): void
    {
        if ($this->originalResolver !== null) {
            throw new GraphQLRuntimeException('You cannot modify the target method via setTargetMethodOnSource because it was already used. You can still wrap the callable using getResolver/setResolver');
        }
        $this->callable = null;
        $this->targetMethodOnSource = null;
        $this->targetPropertyOnSource = $targetPropertyOnSource;
        $this->magicProperty = null;
    }

    public function setMagicProperty(string $magicProperty): void
    {
        if ($this->originalResolver !== null) {
            throw new GraphQLRuntimeException('You cannot modify the target method via setMagicProperty because it was already used. You can still wrap the callable using getResolver/setResolver');
        }
        $this->callable = null;
        $this->targetMethodOnSource = null;
        $this->targetPropertyOnSource = null;
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

    public function getComment(): string|null
    {
        return $this->comment;
    }

    public function setComment(string|null $comment): void
    {
        $this->comment = $comment;
    }

    public function getDeprecationReason(): string|null
    {
        return $this->deprecationReason;
    }

    public function setDeprecationReason(string|null $deprecationReason): void
    {
        $this->deprecationReason = $deprecationReason;
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

        if (is_array($this->callable)) {
            /** @var callable&array{0:object, 1:string} $callable */
            $callable = $this->callable;
            $this->originalResolver = new ServiceResolver($callable);
        } elseif ($this->targetMethodOnSource !== null) {
            $this->originalResolver = new SourceResolver($this->targetMethodOnSource);
        } elseif ($this->targetPropertyOnSource !== null) {
            $this->originalResolver = new SourcePropertyResolver($this->targetPropertyOnSource);
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
