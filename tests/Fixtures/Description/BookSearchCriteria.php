<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Tests\Fixtures\Description;

class BookSearchCriteria
{
    public function __construct(
        public readonly string $title,
    ) {
    }
}
