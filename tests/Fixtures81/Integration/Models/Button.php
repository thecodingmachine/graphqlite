<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures81\Integration\Models;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Type;

/**
 * @Type
 */
class Button
{
    /** @var Color */
    private $color;

    /** @var Size */
    private $size;

    /** @var Position */
    private $state;

    public function __construct(Color $color, Size $size, Position $state)
    {
        $this->color = $color;
        $this->size = $size;
        $this->state = $state;
    }

    /**
     * @Field
     */
    public function getColor(): Color
    {
        return $this->color;
    }

    /**
     * @Field
     */
    public function getSize(): Size
    {
        return $this->size;
    }

    /**
     * @Field
     */
    public function getState(): Position
    {
        return $this->state;
    }
}
