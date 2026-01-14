<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Type;

#[Type(class: TestObject::class)]
class TestTypeWithSetPrefix
{
    #[Field]
    public function settings(): string
    {
        return 'settings';
    }

    #[Field(name: 'numberOfParameters')]
    public function setOfParameters(): int
    {
        return 4;
    }
}
