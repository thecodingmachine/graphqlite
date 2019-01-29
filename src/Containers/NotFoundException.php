<?php


namespace TheCodingMachine\GraphQLite\Containers;

use Psr\Container\NotFoundExceptionInterface;

class NotFoundException extends \RuntimeException implements NotFoundExceptionInterface
{
    public static function notFound(string $id): NotFoundException
    {
        return new self('Could not find entry with ID / type with class "'.$id.'"');
    }

    public static function notFoundInContainer(string $id): NotFoundException
    {
        return new self('GraphQL type "'.$id.'" could not be instantiated automatically. It has a constructor with compulsory parameters. Please create an entry in your container whose name is "'.$id.'"');
    }
}
