<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\Type;
use TheCodingMachine\GraphQLite\Annotations\MiddlewareAnnotations;
use TheCodingMachine\GraphQLite\Middlewares\ServiceResolver;
use TheCodingMachine\GraphQLite\Middlewares\SourceConstructorParameterResolver;
use TheCodingMachine\GraphQLite\Middlewares\SourceInputPropertyResolver;
use TheCodingMachine\GraphQLite\Middlewares\SourceMethodResolver;
use TheCodingMachine\GraphQLite\Parameters\ParameterInterface;
use TheCodingMachine\GraphQLite\Utils\Cloneable;

/**
 * A class that describes a field to be created.
 * To contains getters and setters to alter the field behaviour.
 * It is meant to be passed from middleware to middleware.
 */
class InputFieldDescriptor
{
    use Cloneable;

    /**
     * @param array<string, ParameterInterface> $parameters
     * @param callable $resolver
     * @param bool $injectSource Whether we should inject the source as the first parameter or not.
     */
    public function __construct(
        private readonly string $name,
        private readonly InputType&Type $type,
        private readonly mixed $resolver,
        private readonly SourceInputPropertyResolver|SourceConstructorParameterResolver|SourceMethodResolver|ServiceResolver $originalResolver,
        private readonly array $parameters = [],
        private readonly bool $injectSource = false,
        private readonly bool $forConstructorHydration = false,
        private readonly string|null $comment = null,
        private readonly MiddlewareAnnotations $middlewareAnnotations = new MiddlewareAnnotations([]),
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

    public function isInjectSource(): bool
    {
        return $this->injectSource;
    }

    public function withInjectSource(bool $injectSource): self
    {
        return $this->with(injectSource: $injectSource);
    }

    public function isForConstructorHydration(): bool
    {
        return $this->forConstructorHydration;
    }

    public function withForConstructorHydration(bool $forConstructorHydration): self
    {
        return $this->with(forConstructorHydration: $forConstructorHydration);
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

    /**
     * Returns the original callable that will be used to resolve the field.
     */
    public function getOriginalResolver(): SourceInputPropertyResolver|SourceConstructorParameterResolver|SourceMethodResolver|ServiceResolver
    {
        return $this->originalResolver;
    }

    /**
     * Returns the callable that will be used to evaluate the field. This callable might have been modified to wrap
     * the original callable.
     */
    public function getResolver(): callable
    {
        return $this->resolver;
    }

    public function withResolver(callable $resolver): self
    {
        return $this->with(resolver: $resolver);
    }
}
