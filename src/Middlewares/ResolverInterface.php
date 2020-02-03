<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Middlewares;

/**
 * A class that represents a callable on an object.
 *
 * @internal
 */
interface ResolverInterface
{
    public function getObject(): object;

    public function toString(): string;

    /**
     * @param mixed $args
     *
     * @return mixed
     */
    public function __invoke(...$args);
}
