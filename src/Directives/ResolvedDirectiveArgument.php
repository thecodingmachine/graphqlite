<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Directives;

use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\Type;

/**
 * One directive argument, resolved from a constructor parameter of the directive class. The
 * parameter name doubles as the GraphQL argument name.
 *
 * {@see DirectiveRegistry} builds one per parameter and uses the list to construct the webonyx
 * {@see \GraphQL\Type\Definition\Directive}.
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
