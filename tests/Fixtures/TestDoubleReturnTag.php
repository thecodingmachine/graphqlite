<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Type;

#[Type(class: TestObject::class)]
class TestDoubleReturnTag
{
    /**
     * @return string
     * @return array
     */
    #[Field]
    public function test(): array
    {
        return [];
    }
}
