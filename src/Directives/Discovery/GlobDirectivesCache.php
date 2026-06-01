<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Directives\Discovery;

use TheCodingMachine\GraphQLite\Directives\TypeSystemDirective;

/**
 * Cache entry for a single file produced by {@see DirectiveClassFinder}. Holds the FQCN of the
 * directive class found there (or null when the file contained no directive class).
 *
 * The {@see \TheCodingMachine\GraphQLite\Discovery\Cache\ClassFinderComputedCache} dedupes entries
 * by filename and invalidates them when files change in dev mode.
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
