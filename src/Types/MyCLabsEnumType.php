<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Types;

use GraphQL\Type\Definition\EnumType;
use InvalidArgumentException;
use MyCLabs\Enum\Enum;
use UnexpectedValueException;

/**
 * An extension of the EnumType to support Myclabs enum.
 *
 * This implementation is needed to overwrite the default "serialize" method, that expects to see the exact same object
 * (while there can be several instances of the same enum value with MyclabsEnum)
 */
class MyCLabsEnumType extends EnumType
{
    /** @var string|Enum $enumClassName */
    private $enumClassName;

    public function __construct(string $enumClassName, string $typeName)
    {
        $consts         = $enumClassName::toArray();
        $constInstances = [];
        foreach ($consts as $key => $value) {
            $constInstances[$key] = ['value' => $enumClassName::$key()];
        }

        $this->enumClassName = $enumClassName;

        parent::__construct([
            'name' => $typeName,
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
            try {
                $enumClassName = $this->enumClassName;
                $value = new $enumClassName($value);
            } catch (UnexpectedValueException $exception) {
                throw new InvalidArgumentException('Expected a Myclabs Enum instance', 0, $exception);
            }
        }

        return $value->getKey();
    }
}
