<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\DescriptionItems;

use TheCodingMachine\GraphQLite\Annotations\Cost;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;

class BookController
{
    /** @param int|null $first Number of Books to return */
    #[Query(description: 'Paginated list of Books, optionally filtered')]
    #[Audience('librarians')]
    #[Audience('members')]
    #[Cost(complexity: 5, multipliers: ['first'])]
    public function books(int|null $first = null): bool
    {
        return true;
    }

    /** Counts the Books on loan. */
    #[Query]
    #[Cost(complexity: 2)]
    public function loanedBookCount(): int
    {
        return 0;
    }

    #[Query]
    #[Audience('librarians')]
    #[Cost(complexity: 3)]
    public function undescribedBooks(): bool
    {
        return true;
    }

    /** Internal note, kept out of the schema by the explicit empty description. */
    #[Query(description: '')]
    #[Cost(complexity: 4)]
    public function emptyDescribedBooks(): bool
    {
        return true;
    }

    #[Mutation]
    public function updateBook(BookInput $input): bool
    {
        return true;
    }
}
