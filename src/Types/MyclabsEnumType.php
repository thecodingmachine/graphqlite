<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Types;

use GraphQL\Type\Definition\EnumType;
use MyCLabs\Enum\Enum;
use TheCodingMachine\GraphQLite\GraphQLRuntimeException;

/**
 * An extension of the EnumType to support Myclabs enum.
 *
 * This implementation is needed to overwrite the default "serialize" method, that expects to see the exact same object
 * (while there can be several instances of the same enum value with MyclabsEnum)
 */
class MyclabsEnumType extends EnumType
{
    public function serialize($value)
    {
        if (! $value instanceof Enum) {
            throw new GraphQLRuntimeException('Expected a Myclab Enum instance');
        }
        return $value->getKey();
    }
}
