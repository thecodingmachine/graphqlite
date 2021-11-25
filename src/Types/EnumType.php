<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Types;

use BackedEnum;
use GraphQL\Type\Definition\EnumType as BaseEnumType;
use InvalidArgumentException;
use UnitEnum;

use function assert;
use function is_string;

/**
 * An extension of the EnumType to support native enums.
 */
class EnumType extends BaseEnumType
{
    /** @var bool */
    private $useValues;

    /**
     * @param class-string<UnitEnum> $enumName
     */
    public function __construct(string $enumName, string $typeName, bool $useValues = false)
    {
        $this->useValues = $useValues;

        $values = [];
        foreach ($enumName::cases() as $case) {
            /** @var UnitEnum $case */
            $values[$this->serialize($case)] = ['value' => $case];
        }

        parent::__construct([
            'name' => $typeName,
            'values' => $values,
        ]);
    }

    // phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint

    /**
     * @param mixed $value
     */
    public function serialize($value): string
    {
        if (! $value instanceof UnitEnum) {
            throw new InvalidArgumentException('Expected a Myclabs Enum instance');
        }

        if (! $this->useValues) {
            return $value->name;
        }

        assert($value instanceof BackedEnum);
        assert(is_string($value->value));

        return $value->value;
    }
}
