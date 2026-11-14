<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Tests\Fixtures\Integration\Controllers;

use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\GraphQLite\Tests\Fixtures\Integration\Models\Button;
use TheCodingMachine\GraphQLite\Tests\Fixtures\Integration\Models\Color;
use TheCodingMachine\GraphQLite\Tests\Fixtures\Integration\Models\Position;
use TheCodingMachine\GraphQLite\Tests\Fixtures\Integration\Models\Size;
use TheCodingMachine\GraphQLite\Types\ID;

final class ButtonController
{
    #[Query]
    public function getButton(Color $color, Size $size, Position $state): Button
    {
        return new Button($color, $size, $state);
    }

    #[Mutation]
    public function updateButton(Color $color, Size $size, Position $state): Button
    {
        return new Button($color, $size, $state);
    }

    #[Mutation]
    public function deleteButton(ID $id): void
    {
    }

    #[Mutation]
    public function singleEnum(Size $size): Size
    {
        return $size;
    }
}
