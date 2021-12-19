<?php

namespace TheCodingMachine\GraphQLite\Fixtures;

use Exception;

class TestSourceName
{
    /** @var string */
    private $foo;

    /** @var string */
    private $bar;

    public function __construct(string $foo, string $bar)
    {
        $this->foo = $foo;
        $this->bar = $bar;
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
