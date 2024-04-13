<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Interfaces;

class ClassA
{
    public function __construct(private readonly string $foo)
    {
    }

    public function getFoo(): string
    {
        return $this->foo;
    }
}
