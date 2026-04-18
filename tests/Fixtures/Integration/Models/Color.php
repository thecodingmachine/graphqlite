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
    // A bare #[EnumValue] acknowledges the opt-in migration without altering runtime behaviour.
    // Remove the attribute or annotate specific cases once the enum actually needs per-case
    // description/deprecation metadata.
    #[EnumValue]
    case Green = 'green';
    case Red   = 'red';
}
