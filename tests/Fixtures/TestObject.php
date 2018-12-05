<?php


namespace TheCodingMachine\GraphQL\Controllers\Fixtures;

class TestObject
{
    /**
     * @var string
     */
    private $test;
    /**
     * @var bool
     */
    private $testBool;

    public function __construct(string $test, bool $testBool = false)
    {
        $this->test = $test;
        $this->testBool = $testBool;
    }

    /**
     * @return string
     */
    public function getTest(): string
    {
        return $this->test;
    }

    /**
     * @return bool
     */
    public function isTestBool(): bool
    {
        return $this->testBool;
    }

    /**
     * @return ?string
     */
    public function testRight()
    {
        return "foo";
    }
}
