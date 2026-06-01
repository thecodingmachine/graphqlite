<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\DirectivesIntegration\Inputs;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Input;
use TheCodingMachine\GraphQLite\Directives\BuiltIn\OneOf;

/**
 * Uses the built-in `@oneOf` directive — flips webonyx's `isOneOf` flag so exactly one of `sku`
 * or `id` is required at execution time.
 */
#[Input]
#[OneOf]
final class OneOfLookup
{
    public function __construct(
        #[Field]
        public string|null $sku = null,
        #[Field]
        public int|null $id = null,
    ) {
    }
}
