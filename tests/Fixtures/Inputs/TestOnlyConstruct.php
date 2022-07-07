<?php

namespace TheCodingMachine\GraphQLite\Fixtures\Inputs;

use Exception;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Input;

/**
 * @Input()
 */
class TestOnlyConstruct
{

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

    /**
     * @Field()
     * @var bool
     */
    private $baz;

    public function __construct(string $foo, bool  $baz, int $bar = 100)
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
