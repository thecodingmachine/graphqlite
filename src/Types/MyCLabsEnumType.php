<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Types;

use GraphQL\Type\Definition\EnumType;
use InvalidArgumentException;
use MyCLabs\Enum\Enum;

/**
 * An extension of the EnumType to support Myclabs enum.
 *
 * This implementation is needed to overwrite the default "serialize" method, that expects to see the exact same object
 * (while there can be several instances of the same enum value with MyclabsEnum)
 */
class MyCLabsEnumType extends EnumType
{
    public function __construct(string $enumClassName, string $typeName)
    {
        $consts         = $enumClassName::toArray();
        $constInstances = [];
        foreach ($consts as $key => $value) {
            $constInstances[$key] = ['value' => $enumClassName::$key()];
        }

        parent::__construct([
            'name' => $typeName,
            'values' => $constInstances,
        ]);
    }

    public function serialize(mixed $value): mixed
    {
        if (! $value instanceof Enum) {
            throw new InvalidArgumentException('Expected a Myclabs Enum instance');
        }
        return $value->getKey();
    }
}
