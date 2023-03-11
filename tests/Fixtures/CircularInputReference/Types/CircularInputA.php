<?php

namespace TheCodingMachine\GraphQLite\Fixtures\CircularInputReference\Types;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Input;

/**
 * @Input()
 */
class CircularInputA
{
    /**
     * @Field(inputType="CircularInputBInput")
     * @var CircularInputB
     */
    private $circularInputB;
    private int $bar = 10;

    /** @param CircularInputB $circularInputB */
    public function setCircularInputB($circularInputB): void
    {
        $this->circularInputB = $circularInputB;
    }

    /**
     * @Field
     */
    public function setBar(int $bar): void
    {
        $this->bar = $bar;
    }

    /**
     * @return CircularInputB
     */
    public function getCircularInputB()
    {
        return $this->circularInputB;
    }

    public function getBar(): int
    {
        return $this->bar;
    }
}
