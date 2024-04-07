<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Inputs;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Input;

#[Input]
class TypedFooBar
{
    #[Field]
    public string $foo;

    #[Field]
    public int|null $bar = 10;
}
