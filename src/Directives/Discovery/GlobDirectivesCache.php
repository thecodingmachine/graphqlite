<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Directives\Discovery;

use TheCodingMachine\GraphQLite\Directives\TypeSystemDirective;

/**
 * Cache entry for a directive class {@see DirectiveClassFinder} found.
 *
 * @internal
 */
final class GlobDirectivesCache
{
    /** @param class-string<TypeSystemDirective> $directiveClass */
    public function __construct(public readonly string $directiveClass)
    {
    }
}
