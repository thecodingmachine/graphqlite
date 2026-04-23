<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Models;

use TheCodingMachine\GraphQLite\Annotations\EnumValue;
use TheCodingMachine\GraphQLite\Annotations\Type;

#[Type]
enum Position: int
{
    #[EnumValue]
    case Off = 0;
    #[EnumValue]
    case On  = 1;
}
