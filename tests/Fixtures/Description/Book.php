<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Description;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Type;

/**
 * This docblock summary should NOT leak when an explicit description is provided.
 */
#[Type(name: 'Book', description: 'A library book available for checkout.')]
class Book
{
    public function __construct(
        private readonly string $title,
    ) {
    }

    /**
     * Implementation note — this docblock must be overridden by the explicit #[Field] description.
     */
    #[Field(description: 'The book title as it appears on the cover.')]
    public function getTitle(): string
    {
        return $this->title;
    }
}
