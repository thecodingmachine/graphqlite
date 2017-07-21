<?php


namespace TheCodingMachine\GraphQL\Controllers\Fixtures;

class TestObject
{
    /**
     * @var string
     */
    private $test;

    public function __construct(string $test)
    {
        $this->test = $test;
    }

    /**
     * @return string
     */
    public function getTest(): string
    {
        return $this->test;
    }
}
