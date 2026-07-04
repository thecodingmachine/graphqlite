<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Directives;

use TheCodingMachine\GraphQLite\Annotations\Type;
use TheCodingMachine\GraphQLite\Directives\BuiltIn\OneOf;

/** Misuse: `@oneOf` is an INPUT_OBJECT directive, applied here to an object `#[Type]` class. */
#[Type]
#[OneOf]
final class MisusedOneOfOnType
{
}
