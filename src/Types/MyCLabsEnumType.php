<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Types;

use GraphQL\Type\Definition\EnumType;
use InvalidArgumentException;
use MyCLabs\Enum\Enum;
use function str_replace;

/**
 * An extension of the EnumType to support Myclabs enum.
 *
 * This implementation is needed to overwrite the default "serialize" method, that expects to see the exact same object
 * (while there can be several instances of the same enum value with MyclabsEnum)
 */
class MyCLabsEnumType extends EnumType
{
    public function __construct(string $enumClassName)
    {
        $consts         = $enumClassName::toArray();
        $constInstances = [];
        foreach ($consts as $key => $value) {
            $constInstances[$key] = ['value' => $enumClassName::$key()];
        }

        parent::__construct([
            'name' => 'MyCLabsEnum_' . str_replace('\\', '__', $enumClassName),
            'values' => $constInstances,
        ]);
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function serialize($value)
    {
        if (! $value instanceof Enum) {
            throw new InvalidArgumentException('Expected a Myclabs Enum instance');
        }
        return $value->getKey();
    }
}
