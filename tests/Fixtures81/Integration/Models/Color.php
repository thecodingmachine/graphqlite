<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures81\Integration\Models;

use TheCodingMachine\GraphQLite\Annotations\EnumType;

/**
 * @EnumType(useValues=true)
 */
enum Color: string
{
    case Green = 'green';
    case Red   = 'red';
}
