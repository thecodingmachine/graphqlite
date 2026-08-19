<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\EnumExposureLegacy;

use TheCodingMachine\GraphQLite\Annotations\Type;

/**
 * Fully-unannotated enum: it declares zero #[EnumValue] attributes, so it stays in legacy mode
 * where every case is exposed and case descriptions still fall back to the docblock summary.
 */
#[Type]
enum Weekday: string
{
    /**
     * The first working day of the week.
     */
    case Monday = 'monday';
    case Tuesday = 'tuesday';
    case Wednesday = 'wednesday';
}
