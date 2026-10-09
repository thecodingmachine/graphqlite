<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\DescriptionItems;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Input;
use TheCodingMachine\GraphQLite\Undefined;

#[Input]
class BookInput
{
    /** Title of the Book. */
    #[Field]
    #[Audience('librarians')]
    public string|Undefined $title = Undefined::VALUE;

    #[Field(description: 'Publication date, if any')]
    #[Audience('librarians')]
    public string|null|Undefined $publishedOn = Undefined::VALUE;

    #[Field]
    #[Audience('librarians')]
    public string|null $notes = null;
}
