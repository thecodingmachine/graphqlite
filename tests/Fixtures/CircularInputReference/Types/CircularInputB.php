<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\CircularInputReference\Types;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Input;

#[Input]
class CircularInputB
{
    #[Field(inputType: 'CircularInputAInput')]
    private CircularInputA $circularInputA;

    private int $bar = 10;

    public function setCircularInputB(CircularInputA $circularInputA): void
    {
        $this->circularInputA = $circularInputA;
    }

    #[Field]
    public function setBar(int $bar): void
    {
        $this->bar = $bar;
    }

    public function getCircularInputA()
    {
        return $this->circularInputA;
    }

    public function getBar(): int
    {
        return $this->bar;
    }
}
