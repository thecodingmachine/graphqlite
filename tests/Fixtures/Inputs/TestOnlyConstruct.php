<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Inputs;

use Exception;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Input;

#[Input]
class TestOnlyConstruct
{
    #[Field]
    private string $foo;

    #[Field]
    private int $bar;

    #[Field]
    private bool $baz;

    public function __construct(string $foo, bool $baz, int $bar = 100)
    {
        $this->foo = $foo;
        $this->bar = $bar;
        $this->baz = $baz;
    }

    public function setFoo(string $foo): void
    {
        throw new Exception('This should not be called!');
    }

    public function setBar(int $bar): void
    {
        throw new Exception('This should not be called!');
    }

    public function setBaz(bool $baz): void
    {
        throw new Exception('This should not be called!');
    }

    public function getFoo(): string
    {
        return $this->foo;
    }

    public function getBar(): int
    {
        return $this->bar;
    }

    public function getBaz(): bool
    {
        return $this->baz;
    }
}
