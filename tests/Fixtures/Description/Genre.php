<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Description;

use TheCodingMachine\GraphQLite\Annotations\Type;

/**
 * This docblock MUST be ignored in favor of the explicit description on #[Type].
 */
#[Type(description: 'Editorial classification of a book.')]
enum Genre: string
{
    case Fiction = 'fiction';
    case NonFiction = 'non-fiction';
    case Poetry = 'poetry';
}
