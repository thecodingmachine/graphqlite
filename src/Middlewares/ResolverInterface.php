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

    public function __invoke(mixed ...$args): mixed;
}
