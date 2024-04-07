<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Type;

#[Type(class: TestObject::class)]
class TestTypeWithDescriptions
{
    /** @param string $arg1 Test argument description */
    #[Field]
    public function customField(TestObject $test, string $arg1): string
    {
        return $test->getTest() . $arg1;
    }
}
