<?php


namespace TheCodingMachine\GraphQLite\Fixtures;

use TheCodingMachine\GraphQLite\Annotations\SourceField;
use TheCodingMachine\GraphQLite\Annotations\Type;

/**
 * @Type()
 * @SourceField(name="test")
 */
class TestSelfType
{
    private $foo = 'foo';

    public function test(): string
    {
        return $this->foo;
    }
}
