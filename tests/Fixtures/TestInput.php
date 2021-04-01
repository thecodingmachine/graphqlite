<?php


namespace TheCodingMachine\GraphQLite\Fixtures;

use TheCodingMachine\GraphQLite\Annotations\Input;
use TheCodingMachine\GraphQLite\Annotations\Field;

/**
 * @Input(name="TestInput")
 */
class TestInput
{

    /**
     * @Field()
     * @var string
     */
    public $foo;
}
