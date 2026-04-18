<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Description;

use TheCodingMachine\GraphQLite\Annotations\EnumValue;
use TheCodingMachine\GraphQLite\Annotations\Type;

/**
 * This docblock MUST be ignored in favor of the explicit description on #[Type].
 */
#[Type(description: 'Editorial classification of a book.')]
enum Genre: string
{
    #[EnumValue(description: 'Fiction works including novels and short stories.')]
    case Fiction = 'fiction';

    /**
     * This docblock description should appear on the NonFiction enum value because no
     * #[EnumValue] attribute is declared — it exercises the docblock fallback.
     */
    case NonFiction = 'non-fiction';

    #[EnumValue(deprecationReason: 'Use Fiction::Verse instead.')]
    case Poetry = 'poetry';
}
