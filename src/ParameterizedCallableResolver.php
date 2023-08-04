<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use TheCodingMachine\GraphQLite\Parameters\ParameterInterface;

use function assert;
use function is_callable;
use function is_string;

class ParameterizedCallableResolver
{
    public function __construct(
        private readonly FieldsBuilder $fieldsBuilder,
        private readonly ContainerInterface $container,
    )
    {
    }

    /**
     * @param string|array{class-string, string} $callable
     *
     * @return array{callable, array<string, ParameterInterface>}
     */
    public function resolve(string|array $callable, string|ReflectionClass $classContext, int $skip = 0): array
    {
        if ($classContext instanceof ReflectionClass) {
            $classContext = $classContext->getName();
        }

        // If string method is given, it's equivalent to [self::class, 'method']
        if (is_string($callable)) {
            $callable = [$classContext, $callable];
        }

        try {
            $refMethod = new ReflectionMethod($callable[0], $callable[1]);
        } catch (ReflectionException $e) {
            throw InvalidCallableRuntimeException::methodNotFound($callable[0], $callable[1], $e);
        }

        // If method isn't static, then we should try to resolve the class name through the container.
        if (! $refMethod->isStatic()) {
            $callable = fn (...$args) => $this->container->get($callable[0])->{$callable[1]}(...$args);
        }

        assert(is_callable($callable));

        // Map all parameters of the callable.
        $parameters = $this->fieldsBuilder->getParameters($refMethod, $skip);

        return [$callable, $parameters];
    }
}
