<?php

namespace TheCodingMachine\GraphQLite\Fixtures\Interfaces;


class ClassA
{
    /**
     * @var string
     */
    private $foo;

    public function __construct(string $foo)
    {
        $this->foo = $foo;
    }

    public function getFoo(): string
    {
        return $this->foo;
    }
}