<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Directives\Discovery;

use TheCodingMachine\GraphQLite\Directives\TypeSystemDirective;

/**
 * Cache entry for one file scanned by {@see DirectiveClassFinder}, holding the FQCN of the directive
 * class it found. Files with no directive class get no entry.
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
