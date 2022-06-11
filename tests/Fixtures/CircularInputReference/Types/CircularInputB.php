<?php

namespace TheCodingMachine\GraphQLite\Fixtures\CircularInputReference\Types;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Input;

/**
 * @Input()
 */
class CircularInputB
{

    /**
     * @Field(inputType="CircularInputAInput")
     * @var CircularInputA
     */
    private $circularInputA;

    private int $bar = 10;

    /** @param CircularInputA $circularInputA */
    public function setCircularInputB($circularInputA): void
    {
        $this->circularInputA = $circularInputA;
    }

    /**
     * @Field
     */
    public function setBar(int $bar): void {
        $this->bar = $bar;
    }

    /**
     * @return CircularInputA
     */
    public function getCircularInputA()
    {
        return $this->circularInputA;
    }

    /**
     * @return int
     */
    public function getBar(): int
    {
        return $this->bar;
    }
}
