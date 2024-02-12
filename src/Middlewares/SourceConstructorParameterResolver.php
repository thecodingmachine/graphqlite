<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Middlewares;

/**
 * Resolves field by returning the passed value, so it can later be used in construction of the input type.
 *
 * @internal
 */
class SourceConstructorParameterResolver implements ResolverInterface
{
    /**
     * Constructor
     *
     * @param class-string $className
     */
    public function __construct(
        private readonly string $className,
        private readonly string $parameterName,
    ) {
    }

    /** @return class-string */
    public function className(): string
    {
        return $this->className;
    }

    public function parameterName(): string
    {
        return $this->parameterName;
    }

    public function executionSource(object|null $source): object|null
    {
        return $source;
    }

    public function __invoke(object|null $source, mixed ...$args): mixed
    {
        return $args[0];
    }

    public function toString(): string
    {
        return $this->className . '::__construct($' . $this->parameterName . ')';
    }
}
