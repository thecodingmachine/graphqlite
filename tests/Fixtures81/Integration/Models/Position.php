<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures81\Integration\Models;

use TheCodingMachine\GraphQLite\Annotations\Type;

#[Type]
enum Position: int
{
    case Off = 0;
    case On  = 1;
}
