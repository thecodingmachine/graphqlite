<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\DescriptionLegacyEnum;

use TheCodingMachine\GraphQLite\Annotations\Type;

/**
 * Fixture for the deprecation advisory: this enum intentionally declares zero #[EnumValue]
 * attributes so the advisory fires. Under the future opt-in default it would have no cases
 * exposed at all — which is exactly what the notice is warning about.
 */
#[Type]
enum Era: string
{
    case Classical = 'classical';
    case Modern = 'modern';
}
