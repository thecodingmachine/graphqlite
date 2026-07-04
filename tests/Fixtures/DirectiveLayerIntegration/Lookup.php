<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\DirectiveLayerIntegration;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Input;
use TheCodingMachine\GraphQLite\Directives\BuiltIn\OneOf;

/** Built-in `@oneOf`: callers pass either `sku` or `id`, not both. */
#[Input]
#[OneOf]
final class Lookup
{
    public function __construct(
        #[Field]
        public string|null $sku = null,
        #[Field]
        public int|null $id = null,
    ) {
    }
}
