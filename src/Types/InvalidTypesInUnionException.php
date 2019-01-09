<?php


namespace TheCodingMachine\GraphQL\Controllers\Types;


use TheCodingMachine\GraphQL\Controllers\Mappers\CannotMapTypeExceptionInterface;

class InvalidTypesInUnionException extends \InvalidArgumentException implements CannotMapTypeExceptionInterface
{
    public static function notObjectType(): self
    {
        throw new self('A Union type can only contain objects. Scalars, lists, etc... are not allowed.');
    }
}
