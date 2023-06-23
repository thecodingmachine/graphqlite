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
    /**
     * @param class-string<UnitEnum> $enumName
     * @param array<string, string> $caseDescriptions
     * @param array<string, string> $caseDeprecationReasons
     */
    public function __construct(
        string $enumName,
        string $typeName,
        ?string $description,
        array $caseDescriptions,
        array $caseDeprecationReasons,
        private readonly bool $useValues = false,
    ) {
        $typeValues = [];
        foreach ($enumName::cases() as $case) {
            $key = $this->serialize($case);
            $typeValues[$key] = [
                'name' => $key,
                'value' => $case,
                'description' => $caseDescriptions[$case->name] ?? null,
                'deprecationReason' => $caseDeprecationReasons[$case->name] ?? null,
            ];
        }

        parent::__construct(
            [
                'name' => $typeName,
                'values' => $typeValues,
                'description' => $description,
            ]
        );
    }

    // phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint

    /** @param mixed $value */
    public function serialize($value): string
    {
        if (! $value instanceof UnitEnum) {
            throw new InvalidArgumentException('Expected a UnitEnum instance');
        }

        if (! $this->useValues) {
            return $value->name;
        }

        assert($value instanceof BackedEnum);
        assert(is_string($value->value));

        return $value->value;
    }
}
