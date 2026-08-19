<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use Closure;
use Psr\Container\ContainerInterface;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;

use function is_array;
use function is_string;

/**
 * Normalizes the callable forms an attribute can carry into a {@see Closure}.
 *
 * Attribute arguments are constant expressions, so a behavior-carrying attribute can only name a
 * callable in a handful of ways: an array callable, a bare method name, an invokable object, or,
 * from PHP 8.5, first-class callable syntax and inline static closures. This class turns every one
 * of them into a Closure once, at schema-build time, so the per-request path never branches on the
 * shape it was handed and an unresolvable callable fails at startup with a class and method name in
 * the message rather than inside a resolver.
 *
 * A non-static method named by the array form is resolved through the container, which is the
 * behavior {@see Annotations\Prefetch} has always had. First-class callable syntax cannot express
 * that form, so the array form is not a compatibility shim for older PHP: it is the only syntax
 * that can name a container-resolved method, and it stays.
 */
class CallableResolver
{
    public function __construct(private readonly ContainerInterface $container)
    {
    }

    /**
     * @param string|array{class-string, string}|object $callable An array callable, a bare method
     *                                                            name resolved against $classContext,
     *                                                            an invokable object, or a Closure.
     * @param class-string|null $classContext Class a bare method name is resolved against.
     *
     * @return array{Closure, ReflectionFunctionAbstract} The normalized closure, paired with the
     *                                                    reflection of what it ultimately calls.
     *
     * @throws InvalidCallableRuntimeException
     */
    public function resolve(string|array|object $callable, string|null $classContext = null): array
    {
        if ($callable instanceof Closure) {
            return [$callable, self::reflectClosure($callable)];
        }

        if (! is_array($callable) && ! is_string($callable)) {
            return self::resolveInvokable($callable);
        }

        // A bare method name is equivalent to [$classContext, $method].
        if (is_string($callable)) {
            if ($classContext === null) {
                throw InvalidCallableRuntimeException::noClassContext($callable);
            }

            $callable = [$classContext, $callable];
        }

        [$className, $methodName] = $callable;

        try {
            $refMethod = new ReflectionMethod($className, $methodName);
        } catch (ReflectionException $e) {
            throw InvalidCallableRuntimeException::methodNotFound($className, $methodName, $e);
        }

        if (! $refMethod->isPublic()) {
            throw InvalidCallableRuntimeException::methodNotPublic($className, $methodName);
        }

        if ($refMethod->isStatic()) {
            return [$refMethod->getClosure(), $refMethod];
        }

        // A non-static method is resolved through the container at invocation time, so the service
        // keeps its normal lifecycle instead of being built while the schema is compiled.
        $container = $this->container;
        $closure = static fn (mixed ...$args): mixed => $container->get($className)->$methodName(...$args);

        return [$closure, $refMethod];
    }

    /**
     * @return array{Closure, ReflectionFunctionAbstract}
     *
     * @throws InvalidCallableRuntimeException
     */
    private static function resolveInvokable(object $callable): array
    {
        try {
            $refMethod = new ReflectionMethod($callable, '__invoke');
        } catch (ReflectionException $e) {
            throw InvalidCallableRuntimeException::notInvokable($callable::class, $e);
        }

        if (! $refMethod->isPublic()) {
            throw InvalidCallableRuntimeException::methodNotPublic($callable::class, '__invoke');
        }

        return [$refMethod->getClosure($callable), $refMethod];
    }

    /**
     * Recovers the method a Closure came from, when there is one.
     *
     * First-class callable syntax (`Foo::bar(...)`) produces a Closure that still reports its
     * originating method's name, scope and docblock, so callers that need to reflect the target,
     * such as {@see ParameterizedCallableResolver}, keep working exactly as they do for the array
     * form. An inline closure has no such origin and reflects as a plain function.
     */
    private static function reflectClosure(Closure $closure): ReflectionFunctionAbstract
    {
        $refFunction = new ReflectionFunction($closure);
        $scope = $refFunction->getClosureScopeClass();

        if ($scope !== null && $scope->hasMethod($refFunction->getName())) {
            return $scope->getMethod($refFunction->getName());
        }

        return $refFunction;
    }
}
