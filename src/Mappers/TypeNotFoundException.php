<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Mappers;

use GraphQL\Error\Error;
use GraphQL\Utils\Utils;

/**
 * TypeNotFoundException thrown when RecursiveTypeMapper fails to find a type.
 *
 * While CannotMapTypeException is more about error with configuration this exception can occur when request
 * contains a type which is unknown to the server.
 * Should not be handled by the user, webonyx/graphql-php will transform this under 'errors' key in the response.
 */
class TypeNotFoundException extends Error
{
    public static function createError(string $typeName): Error
    {
        return static::createLocatedError('Unknown type ' . Utils::printSafe($typeName));
    }
}
