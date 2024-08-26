<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Type;
use TheCodingMachine\GraphQLite\Annotations\UseInputType;

#[Type(class: TestObject::class)]
class TestFieldBadInputType
{
    #[Field]
    public function testInput(TestObject $obj, #[UseInputType(inputType: '[NotExists]')]
    $input,): string
    {
        return 'foo';
    }
}
