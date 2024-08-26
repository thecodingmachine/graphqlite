<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures;

class TestObject
{
    public function __construct(private string $test, private bool $testBool = false)
    {
    }

    /**
     * This is a test summary
     */
    public function getTest(): string
    {
        return $this->test;
    }

    public function isTestBool(): bool
    {
        return $this->testBool;
    }

    public function testRight(): string|null
    {
        return 'foo';
    }

    public function getSibling(self $foo): self
    {
        return new self('foo');
    }
}
