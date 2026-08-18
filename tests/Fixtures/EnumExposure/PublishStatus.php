<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\EnumExposure;

use TheCodingMachine\GraphQLite\Annotations\EnumValue;
use TheCodingMachine\GraphQLite\Annotations\Type;

/**
 * Partially-annotated enum: it carries #[EnumValue] on some cases, which puts it in opt-in mode.
 * Only the annotated cases must reach the schema; the unannotated internal cases stay hidden.
 */
#[Type]
enum PublishStatus: string
{
    #[EnumValue(description: 'Visible to everyone.')]
    case Published = 'published';

    #[EnumValue]
    case Scheduled = 'scheduled';

    // Internal-only working state — deliberately left unannotated so it never enters the schema.
    case Draft = 'draft';

    // Internal-only terminal state — likewise hidden.
    case Archived = 'archived';
}
