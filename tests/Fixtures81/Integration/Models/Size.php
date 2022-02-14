<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures81\Integration\Models;

use TheCodingMachine\GraphQLite\Annotations\EnumType;

/**
 * @EnumType
 */
enum Size
{
    case S;
    case M;
    case L;
}
