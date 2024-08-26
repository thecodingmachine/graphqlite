<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures;

use Exception;

class TestSourceName
{
    public function __construct(private string $foo, private string $bar)
    {
    }

    public function __get($name)
    {
        if ($name !== 'foo') {
            throw new Exception('Unknown property: ' . $name);
        }

        return $this->$name;
    }

    public function getBar(): string
    {
        return $this->bar;
    }
}
