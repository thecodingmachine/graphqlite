<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Mappers\Parameters;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Type;
use Psr\Container\ContainerInterface;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;
use TheCodingMachine\GraphQLite\Annotations\ParameterAnnotations;
use TheCodingMachine\GraphQLite\Annotations\Prefetch;
use TheCodingMachine\GraphQLite\FieldsBuilder;
use TheCodingMachine\GraphQLite\InvalidPrefetchMethodRuntimeException;
use TheCodingMachine\GraphQLite\Parameters\ParameterInterface;
use TheCodingMachine\GraphQLite\Parameters\PrefetchDataParameter;

use function assert;
use function is_callable;
use function is_string;

/**
 * Handles {@see Prefetch} annotated parameters.
 */
class PrefetchParameterHandler implements ParameterMiddlewareInterface
{
    public function __construct(
        private readonly FieldsBuilder $fieldsBuilder,
        private readonly ContainerInterface $container,
    )
    {
    }

    public function mapParameter(ReflectionParameter $parameter, DocBlock $docBlock, Type|null $paramTagType, ParameterAnnotations $parameterAnnotations, ParameterHandlerInterface $next): ParameterInterface
    {
        $prefetch = $parameterAnnotations->getAnnotationByType(Prefetch::class);

        if ($prefetch === null) {
            return $next->mapParameter($parameter, $docBlock, $paramTagType, $parameterAnnotations);
        }

        [$resolver, $parameters] = $this->parseResolver($prefetch, $parameter);

        return new PrefetchDataParameter(
            fieldName: $parameter->getDeclaringFunction()->getName(),
            resolver: $resolver,
            parameters: $parameters,
        );
    }

    /** @return array{callable, array<string, ParameterInterface>} */
    private function parseResolver(Prefetch $prefetch, ReflectionParameter $parameter): array
    {
        $declaringMethod = $parameter->getDeclaringFunction();

        assert($declaringMethod instanceof ReflectionMethod);

        // TODO: in a resolver-refactor PR that follows this can be simplified and improved upon with
        // possibly better error messages and less duplication. For now this will do.
        $resolver = is_string($prefetch->callable) ?
            [$declaringMethod->getDeclaringClass()->getName(), $prefetch->callable] :
            $prefetch->callable;

        try {
            $refMethod = new ReflectionMethod($resolver[0], $resolver[1]);
        } catch (ReflectionException $e) {
            throw InvalidPrefetchMethodRuntimeException::methodNotFound(
                $declaringMethod,
                $declaringMethod->getDeclaringClass(),
                $resolver[1],
                $e,
            );
        }

        if (! $refMethod->isStatic()) {
            $resolver = fn (...$args) => $this->container->get($resolver[0])->{$resolver[1]}(...$args);
        }

        assert(is_callable($resolver));

        // Map all parameters of the prefetch method. Skip first one as it will always be an array of sources.
        $parameters = $this->fieldsBuilder->getParameters($refMethod, 1);

        return [$resolver, $parameters];
    }
}
