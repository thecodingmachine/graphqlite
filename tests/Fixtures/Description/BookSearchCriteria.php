<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Description;

class BookSearchCriteria
{
    public function __construct(
        public readonly string $title,
    ) {
    }
}
