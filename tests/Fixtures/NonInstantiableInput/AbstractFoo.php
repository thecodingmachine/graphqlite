<?php

namespace TheCodingMachine\GraphQLite\Fixtures\NonInstantiableInput;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Input;

/**
 * @Input()
 */
abstract class AbstractFoo
{

    /**
     * @Field()
     * @var string
     */
    public $foo = 'bar';
}
