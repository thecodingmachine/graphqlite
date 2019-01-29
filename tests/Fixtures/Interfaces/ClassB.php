<?php

namespace TheCodingMachine\GraphQLite\Fixtures\Interfaces;


class ClassB extends ClassA
{
    /**
     * @var string
     */
    private $bar;

    public function __construct(string $foo, string $bar)
    {
        parent::__construct($foo);

        $this->bar = $bar;
    }

    public function getBar(): string
    {
        return $this->bar;
    }
}
