<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\DescriptionDuplicate;

use TheCodingMachine\GraphQLite\Annotations\ExtendType;
use TheCodingMachine\GraphQLite\Annotations\Field;

/**
 * Deliberate conflict: the base #[Type] already provided a description, so the extension
 * providing another one MUST trigger DuplicateDescriptionOnTypeException at schema build time.
 */
#[ExtendType(class: Book::class, description: 'Second description from #[ExtendType].')]
class BookExtension
{
    #[Field]
    public function extraField(Book $book): string
    {
        return 'extra';
    }
}
