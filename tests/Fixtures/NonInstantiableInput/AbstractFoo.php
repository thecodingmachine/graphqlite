<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\NonInstantiableInput;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Input;

#[Input]
abstract class AbstractFoo
{
    #[Field]
    public string $foo = 'bar';
}
