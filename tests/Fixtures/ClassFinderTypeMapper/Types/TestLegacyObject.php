<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Tests\Fixtures\ClassFinderTypeMapper\Types;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Type;

#[Type]
class TestLegacyObject
{
    #[Field]
    public function getFoo(): int
    {
        return 42;
    }
}
