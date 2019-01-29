<?php


namespace TheCodingMachine\GraphQLite\Fixtures;

class TestObject2
{
    /**
     * @var string
     */
    private $test2;

    public function __construct(string $test2)
    {
        $this->test2 = $test2;
    }

    /**
     * @return string
     */
    public function getTest2(): string
    {
        return $this->test2;
    }
}
