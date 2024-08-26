<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Types;

use TheCodingMachine\GraphQLite\Annotations\ExtendType;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Fixtures\TestObject;

use function strtoupper;

#[ExtendType(class: TestObject::class)]
class FooExtendType
{
    #[Field]
    public function customExtendedField(TestObject $test): string
    {
        return strtoupper($test->getTest());
    }
}
