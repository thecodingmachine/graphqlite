<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\DescriptionDuplicate;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Type;

#[Type(name: 'DuplicateBook', description: 'First description from #[Type].')]
class Book
{
    #[Field]
    public function title(): string
    {
        return 'Title';
    }
}
