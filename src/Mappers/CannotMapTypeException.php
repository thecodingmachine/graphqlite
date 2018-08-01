<?php


namespace TheCodingMachine\GraphQL\Controllers\Mappers;


class CannotMapTypeException extends \Exception
{
    public static function createForType(string $className)
    {
        return new self('Cannot map class "'.$className.'" to a known GraphQL type. Check your TypeMapper configuration.');
    }

    public static function createForInputType(string $className)
    {
        return new self('Cannot map class "'.$className.'" to a known GraphQL input type. Check your TypeMapper configuration.');
    }
}