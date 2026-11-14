<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Tests\Fixtures\InheritedInputTypes;

use TheCodingMachine\GraphQLite\Annotations\Factory;
use TheCodingMachine\GraphQLite\Tests\Fixtures\TestObject;

abstract class AbstractTestFactory
{
    #[Factory]
    public function myFactory(string $string, bool $bool = true): TestObject
    {
        return new TestObject($string, $bool);
    }
}
