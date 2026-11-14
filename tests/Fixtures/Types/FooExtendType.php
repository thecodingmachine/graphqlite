<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Tests\Fixtures\Types;

use TheCodingMachine\GraphQLite\Annotations\ExtendType;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Tests\Fixtures\TestObject;

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
