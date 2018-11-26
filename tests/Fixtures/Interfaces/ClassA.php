<?php

namespace TheCodingMachine\GraphQL\Controllers\Fixtures\Interfaces;


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