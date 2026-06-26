<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\DirectiveLayerIntegration;

use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\GraphQLite\Directives\BuiltIn\Deprecated;

final class DirectiveLayerController
{
    // Built-in @deprecated applied as an attribute, through the directive middleware.
    #[Query]
    #[Deprecated(reason: 'Use current instead.')]
    public function legacy(): string
    {
        return 'legacy';
    }

    #[Query]
    public function lookup(Lookup $lookup): string
    {
        return $lookup->sku ?? (string) $lookup->id;
    }

    // A bare #[Deprecated] should keep the docblock @deprecated reason rather than overriding it.
    /** @deprecated Use lookup instead. */
    #[Query]
    #[Deprecated]
    public function legacyDocblock(): string
    {
        return 'legacy';
    }
}
