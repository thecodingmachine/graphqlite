<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\HideIfUnauthorized;
use TheCodingMachine\GraphQLite\Annotations\Logged;
use TheCodingMachine\GraphQLite\Annotations\Right;
use TheCodingMachine\GraphQLite\Annotations\SourceField;
use TheCodingMachine\GraphQLite\Annotations\Type;

#[Type(class: TestObject::class)]
#[SourceField(name: 'test')]
#[SourceField(name: 'testBool', annotations: [new Logged(), new HideIfUnauthorized()])]
#[SourceField(name: 'testRight', annotations: [new Right('FOOBAR'), new HideIfUnauthorized()])]
#[SourceField(name: 'sibling', description: 'Test SourceField description')]
class TestType
{
    #[Field]
    public function customField(TestObject $test, string $param = 'foo'): string
    {
        return $test->getTest() . $param;
    }
}
