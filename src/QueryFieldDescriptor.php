<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\Type;
use TheCodingMachine\GraphQLite\Annotations\MiddlewareAnnotations;
use TheCodingMachine\GraphQLite\Middlewares\MagicPropertyResolver;
use TheCodingMachine\GraphQLite\Middlewares\ServiceResolver;
use TheCodingMachine\GraphQLite\Middlewares\SourceMethodResolver;
use TheCodingMachine\GraphQLite\Middlewares\SourcePropertyResolver;
use TheCodingMachine\GraphQLite\Parameters\ParameterInterface;
use TheCodingMachine\GraphQLite\Utils\Cloneable;

/**
 * A class that describes a field to be created.
 * To contains getters and setters to alter the field behaviour.
 * It is meant to be passed from middleware to middleware.
 */
class QueryFieldDescriptor
{
    use Cloneable;

    /**
     * @param array<string, ParameterInterface> $parameters
     * @param callable $resolver
     * @param bool $injectSource Whether we should inject the source as the first parameter or not.
     */
    public function __construct(
        private readonly string $name,
        private readonly OutputType&Type $type,
        private readonly mixed $resolver,
        private readonly SourcePropertyResolver|MagicPropertyResolver|SourceMethodResolver|ServiceResolver $originalResolver,
        private readonly array $parameters = [],
        private readonly bool $injectSource = false,
        private readonly string|null $comment = null,
        private readonly string|null $deprecationReason = null,
        private readonly MiddlewareAnnotations $middlewareAnnotations = new MiddlewareAnnotations([]),
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

    public function withAddedCommentLines(string $comment): self
    {
        if (! $this->comment) {
            return $this->withComment($comment);
        }

        return $this->withComment($this->comment . "\n" . $comment);
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

    /**
     * Returns the original callable that will be used to resolve the field.
     */
    public function getOriginalResolver(): SourcePropertyResolver|MagicPropertyResolver|SourceMethodResolver|ServiceResolver
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
