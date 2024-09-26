<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Mappers\Parameters;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Type;
use ReflectionMethod;
use ReflectionParameter;
use TheCodingMachine\GraphQLite\Annotations\ParameterAnnotations;
use TheCodingMachine\GraphQLite\Annotations\Prefetch;
use TheCodingMachine\GraphQLite\InvalidCallableRuntimeException;
use TheCodingMachine\GraphQLite\InvalidPrefetchMethodRuntimeException;
use TheCodingMachine\GraphQLite\ParameterizedCallableResolver;
use TheCodingMachine\GraphQLite\Parameters\ParameterInterface;
use TheCodingMachine\GraphQLite\Parameters\PrefetchDataParameter;

use function assert;

/**
 * Handles {@see Prefetch} annotated parameters.
 */
class PrefetchParameterMiddleware implements ParameterMiddlewareInterface
{
    public function __construct(
        private readonly ParameterizedCallableResolver $parameterizedCallableResolver,
    )
    {
    }

    public function mapParameter(ReflectionParameter $parameter, DocBlock $docBlock, Type|null $paramTagType, ParameterAnnotations $parameterAnnotations, ParameterHandlerInterface $next): ParameterInterface
    {
        $prefetch = $parameterAnnotations->getAnnotationByType(Prefetch::class);

        if ($prefetch === null) {
            return $next->mapParameter($parameter, $docBlock, $paramTagType, $parameterAnnotations);
        }

        $method = $parameter->getDeclaringFunction();

        assert($method instanceof ReflectionMethod);

        // Map callable specified by #[Prefetch] into a real callable and parse all of the GraphQL parameters.
        try {
            [$resolver, $parameters] = $this->parameterizedCallableResolver->resolve($prefetch->callable, $method->getDeclaringClass(), 1);
        } catch (InvalidCallableRuntimeException $e) {
            throw InvalidPrefetchMethodRuntimeException::fromInvalidCallable($method, $parameter->getName(), $e);
        }

        return new PrefetchDataParameter(
            fieldName: $method->getName(),
            resolver: $resolver,
            parameters: $parameters,
        );
    }
}
