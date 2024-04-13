<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Interfaces;

class ClassB extends ClassA
{
    public function __construct(string $foo, private readonly string $bar)
    {
        parent::__construct($foo);
    }

    public function getBar(): string
    {
        return $this->bar;
    }
}
