<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Directives;

use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\Type;

/**
 * A single directive argument resolved from a directive class's constructor parameter.
 *
 * Stores the GraphQL input type, the PHP parameter name (which is also the GraphQL argument name),
 * an optional description, and the default value (if any). The {@see DirectiveRegistry} builds one
 * of these per constructor parameter at validation time, then uses the list to construct the
 * webonyx {@see \GraphQL\Type\Definition\Directive} that's registered with the schema.
 *
 * @internal
 */
final class ResolvedDirectiveArgument
{
    public function __construct(
        public readonly string $name,
        public readonly InputType&Type $type,
        public readonly bool $hasDefaultValue,
        public readonly mixed $defaultValue,
        public readonly string|null $description = null,
    ) {
    }
}
