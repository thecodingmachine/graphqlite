<?php

namespace TheCodingMachine\GraphQLite\Fixtures\Inputs;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Input;

#[Input]
class InputWithSetter
{

    #[Field]
    private string $foo;


    private int $bar = 10;

    public function setFoo(string $foo): void
    {
        $this->foo = $foo;
    }

    #[Field]
    public function setBar(int $bar): void {
        $this->bar = $bar;
    }

    /**
     * @return string
     */
    public function getFoo(): string
    {
        return $this->foo;
    }

    /**
     * @return int
     */
    public function getBar(): int
    {
        return $this->bar;
    }
}
