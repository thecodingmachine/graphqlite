<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Directives;

use GraphQL\Type\Definition\Directive as WebonyxDirective;

/**
 * What {@see DirectiveResolver} produces for one directive class: its metadata, its resolved
 * constructor arguments, and the webonyx directive to register on the schema. The directive is null
 * for built-ins, which webonyx already declares.
 *
 * @internal
 */
final class ResolvedDirective
{
    /** @param list<ResolvedDirectiveArgument> $arguments */
    public function __construct(
        public readonly DirectiveDefinition $definition,
        public readonly array $arguments,
        public readonly WebonyxDirective|null $webonyxDirective,
    ) {
    }
}
