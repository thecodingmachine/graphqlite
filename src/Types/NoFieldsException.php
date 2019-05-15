<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Types;

use UnderflowException;

class NoFieldsException extends UnderflowException
{
    public static function create(string $name): self
    {
        return new self('The GraphQL object type "' . $name . '" has no fields defined. Please check that some fields are defined (using the @Field annotation). If some fields are defined, please check that at least one is visible to the current user.');
    }
}
