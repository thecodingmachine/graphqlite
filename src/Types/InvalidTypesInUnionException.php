<?php


namespace TheCodingMachine\GraphQLite\Types;


use TheCodingMachine\GraphQLite\Mappers\CannotMapTypeExceptionInterface;

class InvalidTypesInUnionException extends \InvalidArgumentException implements CannotMapTypeExceptionInterface
{
    public static function notObjectType(): self
    {
        throw new self('A Union type can only contain objects. Scalars, lists, etc... are not allowed.');
    }
}
