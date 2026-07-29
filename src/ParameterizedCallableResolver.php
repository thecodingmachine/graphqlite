<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use Closure;
use ReflectionClass;
use ReflectionMethod;
use TheCodingMachine\GraphQLite\Parameters\ParameterInterface;

class ParameterizedCallableResolver
{
    public function __construct(
        private readonly FieldsBuilder $fieldsBuilder,
        private readonly CallableResolver $callableResolver,
    )
    {
    }

    /**
     * Resolves a callable and maps the GraphQL parameters it declares.
     *
     * @param string|array{class-string, string}|object $callable
     * @param class-string|ReflectionClass<object> $classContext
     *
     * @return array{Closure, array<string, ParameterInterface>}
     *
     * @throws InvalidCallableRuntimeException
     */
    public function resolve(string|array|object $callable, string|ReflectionClass $classContext, int $skip = 0): array
    {
        if ($classContext instanceof ReflectionClass) {
            $classContext = $classContext->getName();
        }

        [$resolved, $refFunction] = $this->callableResolver->resolve($callable, $classContext);

        // Parameters become GraphQL arguments and their descriptions come from the method docblock,
        // so the target has to be a real method. First-class callable syntax keeps that origin and
        // reflects as a ReflectionMethod; an inline closure does not and cannot be mapped.
        // Callers add their own context by catching InvalidCallableRuntimeException, the way
        // PrefetchParameterMiddleware rewraps it as InvalidPrefetchMethodRuntimeException.
        if (! $refFunction instanceof ReflectionMethod) {
            throw InvalidCallableRuntimeException::notAMethod();
        }

        return [$resolved, $this->fieldsBuilder->getParameters($refFunction, $skip)];
    }
}
