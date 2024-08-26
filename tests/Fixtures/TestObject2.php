<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures;

class TestObject2
{
    public function __construct(private string $test2)
    {
    }

    public function getTest2(): string
    {
        return $this->test2;
    }
}
