<?php


namespace TheCodingMachine\GraphQLite\Tests\Fixtures;

use TheCodingMachine\GraphQLite\Annotations\SourceField;
use TheCodingMachine\GraphQLite\Annotations\Type;

#[Type]
#[SourceField(name: 'test')]
class TestSelfType
{
    private $foo = 'foo';

    public function test(): string
    {
        return $this->foo;
    }
}
