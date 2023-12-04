<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Middlewares;

use function get_class;

/**
 * Resolves field by calling a callable.
 *
 * @internal
 */
final class ServiceResolver implements ResolverInterface
{
    /** @var callable&array{0:object, 1:string} */
    private $callable;

    /** @param callable&array{0:object, 1:string} $callable */
    public function __construct(callable $callable)
    {
        $this->callable = $callable;
    }

    /** @return callable&array{0:object, 1:string} */
    public function callable(): callable
    {
        return $this->callable;
    }

    public function executionSource(object|null $source): object
    {
        return $this->callable[0];
    }

    public function __invoke(object|null $source, mixed ...$args): mixed
    {
        $callable = $this->callable;

        return $callable(...$args);
    }

    public function toString(): string
    {
        $class = get_class($this->callable[0]);

        return $class . '::' . $this->callable[1] . '()';
    }
}
