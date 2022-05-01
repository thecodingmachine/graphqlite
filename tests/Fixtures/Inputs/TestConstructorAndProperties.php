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

    public function __construct(\DateTimeImmutable $date)
    {
        $this->date = $date;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function setFoo(string $foo): void
    {
        $this->foo = $foo;
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
