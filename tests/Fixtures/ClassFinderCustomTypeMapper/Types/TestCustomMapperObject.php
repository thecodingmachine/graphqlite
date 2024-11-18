<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\ClassFinderCustomTypeMapper\Types;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Type;

#[Type]
final class TestCustomMapperObject
{
    #[Field]
    public function getFoo(): int
    {
        return 42;
    }
}
