<?php

namespace TheCodingMachine\GraphQLite\Fixtures\Inputs;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Input;

/**
 * @Input()
 * @Input(name="ForcedTypeInput", update=true)
 */
class InputWithSetter
{
    /** @Field() */
    private string $foo;
    private int $bar = 10;

    public function setFoo(string $foo): void
    {
        $this->foo = $foo;
    }

    /**
     * @Field(for="InputWithSetterInput")
     * @Field(for="ForcedTypeInput", inputType="Int!")
     */
    public function setBar(int $bar): void
    {
        $this->bar = $bar;
    }

    public function getFoo(): string
    {
        return $this->foo;
    }

    public function getBar(): int
    {
        return $this->bar;
    }
}
