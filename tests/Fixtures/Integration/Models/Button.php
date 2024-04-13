<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Models;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Type;

#[Type]
class Button
{
    public function __construct(private Color $color, private Size $size, private Position $state)
    {
    }

    #[Field]
    public function getColor(): Color
    {
        return $this->color;
    }

    #[Field]
    public function getSize(): Size
    {
        return $this->size;
    }

    #[Field]
    public function getState(): Position
    {
        return $this->state;
    }
}
