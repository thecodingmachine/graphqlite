<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Models;

use TheCodingMachine\GraphQLite\Annotations\EnumValue;
use TheCodingMachine\GraphQLite\Annotations\Type;

#[Type]
enum Size
{
    #[EnumValue]
    case S;
    #[EnumValue]
    case M;
    #[EnumValue]
    case L;
}
