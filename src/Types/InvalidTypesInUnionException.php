<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Types;

use InvalidArgumentException;
use TheCodingMachine\GraphQLite\Mappers\CannotMapTypeExceptionInterface;
use TheCodingMachine\GraphQLite\Mappers\CannotMapTypeTrait;

class InvalidTypesInUnionException extends InvalidArgumentException implements CannotMapTypeExceptionInterface
{
    use CannotMapTypeTrait;

    public static function notObjectType(): self
    {
        throw new self('A Union type can only contain objects. Scalars, lists, etc... are not allowed.');
    }
}
