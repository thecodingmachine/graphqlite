<?php

namespace TheCodingMachine\GraphQLite\Fixtures\Inputs;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Input;

/**
 * @Input()
 */
class TypedFooBar
{

    /**
     * @Field()
     */
    public string $foo;

    /**
     * @Field()
     */
    public ?int $bar = 10;
}
