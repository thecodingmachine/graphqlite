<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Types;

use UnitEnum;

/**
 * A single enum case exposed in the GraphQL schema, paired with its resolved metadata.
 *
 * @internal Built by EnumTypeMapper and consumed by EnumType; not part of the public API.
 */
final class ExposedEnumCase
{
    public function __construct(
        public readonly UnitEnum $case,
        public readonly string|null $description,
        public readonly string|null $deprecationReason,
    ) {
    }
}
