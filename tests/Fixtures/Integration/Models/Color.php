<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Models;

use TheCodingMachine\GraphQLite\Annotations\EnumValue;
use TheCodingMachine\GraphQLite\Annotations\Type;

#[Type(
    name: 'Color',
    useEnumValues: true,
)]
enum Color: string
{
    #[EnumValue]
    case Green = 'green';
    #[EnumValue]
    case Red   = 'red';
}
