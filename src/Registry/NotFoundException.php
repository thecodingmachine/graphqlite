<?php


namespace TheCodingMachine\GraphQL\Controllers\Registry;

use Psr\Container\NotFoundExceptionInterface;

class NotFoundException extends \RuntimeException implements NotFoundExceptionInterface
{
    public static function notFound(string $id): NotFoundException
    {
        return new self('Could not find entry with ID / type with class "'.$id.'"');
    }
}
