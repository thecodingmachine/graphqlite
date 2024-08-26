<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Types;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Logged;
use TheCodingMachine\GraphQLite\Annotations\Right;
use TheCodingMachine\GraphQLite\Annotations\SourceField;
use TheCodingMachine\GraphQLite\Fixtures\TestObject;

#[SourceField(name: 'test')]
#[SourceField(name: 'testBool', annotations: [new Logged()])]
#[SourceField(name: 'testRight', annotations: [new Right(name: 'FOOBAR')])]
abstract class AbstractFooType
{
    #[Field]
    public function customField(TestObject $test, string $param = 'foo'): string
    {
        return $test->getTest() . $param;
    }
}
