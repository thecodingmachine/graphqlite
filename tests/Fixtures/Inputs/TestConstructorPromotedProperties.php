<?php

namespace TheCodingMachine\GraphQLite\Fixtures\Inputs;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Input;
use TheCodingMachine\GraphQLite\Annotations\Right;

#[Input]
class TestConstructorPromotedProperties
{
    #[Field]
    private int $bar;

    public function __construct(
        #[Field]
        private readonly \DateTimeImmutable $date,
        #[Field]
        #[Right('FOOOOO')]
        public string $foo
    )
    {
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function setFoo(string $foo): void
    {
        throw new \RuntimeException("This should not be called");
    }

    public function setBar(int $bar): void
    {
        $this->bar = $bar;
    }

    public function getBar(): int
    {
        return $this->bar;
    }
}