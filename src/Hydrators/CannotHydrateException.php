<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Hydrators;

use Exception;

class CannotHydrateException extends Exception
{
    public static function createForInputType(string $inputTypeName) : self
    {
        return new self('Cannot hydrate type "' . $inputTypeName . '"');
    }
}
