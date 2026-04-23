<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Description;

use TheCodingMachine\GraphQLite\Annotations\ExtendType;
use TheCodingMachine\GraphQLite\Annotations\Field;

/**
 * Extension class — the base #[Type(class: Author::class)] does NOT declare a description, so this
 * #[ExtendType] is the only source for the Author type's description. This is a legitimate use case
 * and must not trigger the duplicate-description exception.
 */
#[ExtendType(class: Author::class, description: 'A person who writes books.')]
class AuthorExtension
{
    #[Field(description: 'The number of books published by this author.')]
    public function bookCount(Author $author): int
    {
        return 3;
    }
}
