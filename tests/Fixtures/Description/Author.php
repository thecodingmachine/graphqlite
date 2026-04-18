<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Description;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Type;

/**
 * Author docblock that SHOULD populate the schema description when docblock fallback is enabled
 * and no explicit description was provided on the #[Type] attribute.
 */
#[Type(name: 'Author')]
class Author
{
    public function __construct(
        private readonly string $name,
    ) {
    }

    /**
     * Docblock summary that should populate the field description via the fallback path.
     */
    #[Field]
    public function getName(): string
    {
        return $this->name;
    }
}
