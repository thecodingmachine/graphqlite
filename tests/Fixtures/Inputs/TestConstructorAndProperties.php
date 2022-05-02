<?php

namespace TheCodingMachine\GraphQLite\Fixtures\Inputs;

use Exception;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Input;

/**
 * @Input()
 */
class TestConstructorAndProperties
{

    /**
     * @Field()
     */
    private \DateTimeImmutable $date;

    /**
     * @Field()
     * @var string
     */
    private $foo;

    /**
     * @Field()
     * @var int
     */
    private $bar;

    public function __construct(\DateTimeImmutable $date, string $foo)
    {
        $this->date = $date;
        $this->foo = $foo;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function setFoo(string $foo): void
    {
        throw new \RuntimeException("This should not be called");
    }

    public function getFoo(): string
    {
        return $this->foo;
    }

    public function setBar(int $bar): void
    {
        $this->bar = $bar;
    }

    public function getBar(): int
    {
        return $this->bar;
    }
}
