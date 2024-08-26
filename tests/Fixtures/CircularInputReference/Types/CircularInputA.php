<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\CircularInputReference\Types;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Input;

#[Input]
class CircularInputA
{
    #[Field(inputType: 'CircularInputBInput')]
    private CircularInputB $circularInputB;

    private int $bar = 10;

    public function setCircularInputB(CircularInputB $circularInputB): void
    {
        $this->circularInputB = $circularInputB;
    }

    #[Field]
    public function setBar(int $bar): void
    {
        $this->bar = $bar;
    }

    public function getCircularInputB(): CircularInputB
    {
        return $this->circularInputB;
    }

    public function getBar(): int
    {
        return $this->bar;
    }
}
