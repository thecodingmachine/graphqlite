<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Inputs;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Input;

#[Input]
#[Input(name: 'FooBarUpdateInput', update: true)]
class FooBar
{
    /**
     * Foo comment.
     */
    #[Field(description: 'Foo description.')]
    public string $foo;

    /**
     * Bar comment.
     */
    #[Field]
    public string|null $bar = 'bar';

    #[Field(for: 'FooBarUpdateInput', name: 'timestamp')]
    public string|null $date = null;

    public function __construct(string $foo, string|null $bar = 'test')
    {
        $this->foo = $foo;
        $this->bar = $bar;
    }
}
