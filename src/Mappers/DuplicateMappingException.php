<?php


namespace TheCodingMachine\GraphQLite\Mappers;


class DuplicateMappingException extends \RuntimeException
{
    public static function createForType(string $sourceClass, string $type1, string $type2): self
    {
        throw new self("The class '$sourceClass' should be mapped by only one GraphQL type class. Two classes are pointing via the @Type annotation to this class: '$type1' and '$type2'");
    }

    public static function createForFactory(string $sourceClass, string $className1, string $method1, string $className2, string $method2): self
    {
        throw new self("The class '$sourceClass' should be mapped to only one GraphQL Input type. Two methods are pointing via the @Factory annotation to this class: '$className1::$method1' and '$className2::$method2'");
    }

    public static function createForTypeName(string $type, string $sourceClass1, string $sourceClass2): self
    {
        throw new self("The type '$type' is created by 2 different classes: '$sourceClass1' and '$sourceClass2'");
    }
}
