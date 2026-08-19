<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\StripFieldPrefixes;

use TheCodingMachine\GraphQLite\Annotations\SourceField;
use TheCodingMachine\GraphQLite\Annotations\Type;

#[Type(class: Product::class)]
#[SourceField(name: 'name')]
#[SourceField(name: 'stock')]
class ProductType
{
}
