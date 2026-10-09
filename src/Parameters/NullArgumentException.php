<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Parameters;

use TheCodingMachine\GraphQLite\Exceptions\GraphQLException;

/**
 * An explicit `null` was passed for an argument or input field typed `T|Undefined`.
 *
 * GraphQL has no way to say "may be omitted but not null", so such a type is exposed as nullable and the
 * null has to be refused here instead.
 */
class NullArgumentException extends GraphQLException
{
    public static function create(string $argumentName): self
    {
        return new self("Argument '" . $argumentName . "' may be omitted but cannot be null");
    }
}
