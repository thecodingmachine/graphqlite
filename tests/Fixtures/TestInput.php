<?php

namespace TheCodingMachine\GraphQLite\Fixtures;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Input;

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
