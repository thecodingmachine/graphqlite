<?php

namespace TheCodingMachine\GraphQLite\Fixtures\Inputs;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Input;

/**
 * @Input()
 * @Input(name="FooBarUpdateInput", update=true)
 */
class FooBar
{

    /**
     * Foo comment.
     *
     * @Field(description="Foo description.")
     */
    public string $foo;

    /**
     * Bar comment.
     *
     * @Field()
     */
    public ?string $bar = 'bar';

    /**
     * @Field(for="FooBarUpdateInput", name="timestamp")
     */
    public ?string $date;

    /**
     * FooBar constructor.
     *
     * @param string      $foo
     * @param string|null $bar
     */
    public function __construct(string $foo, ?string $bar = 'test')
    {
        $this->foo = $foo;
        $this->bar = $bar;
    }
}
