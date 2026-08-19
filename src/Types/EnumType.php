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
 *
 * @internal The constructor shape (an explicit list of exposed cases with resolved metadata) is a
 *     framework-internal contract with EnumTypeMapper, not a public API.
 */
class EnumType extends BaseEnumType
{
    /**
     * @param list<ExposedEnumCase> $cases The enum cases that participate in the schema, each
     *     paired with its resolved metadata.
     */
    public function __construct(
        array $cases,
        string $typeName,
        string|null $description,
        private readonly bool $useValues = false,
    ) {
        $typeValues = [];
        foreach ($cases as $exposed) {
            $key = $this->serialize($exposed->case);
            $typeValues[$key] = [
                'name' => $key,
                'value' => $exposed->case,
                'description' => $exposed->description,
                'deprecationReason' => $exposed->deprecationReason,
            ];
        }

        parent::__construct([
            'name' => $typeName,
            'values' => $typeValues,
            'description' => $description,
        ]);
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
