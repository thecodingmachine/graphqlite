<?php


namespace TheCodingMachine\GraphQL\Controllers\Mappers;


class DuplicateMappingException extends \RuntimeException
{
    public static function create(string $sourceClass, string $type1, string $type2): self
    {
        throw new self("The class '$sourceClass' should be mapped by only one GraphQL type class. Two classes are pointing via the @Type annotation to this class: '$type1' and '$type2'");
    }
}
