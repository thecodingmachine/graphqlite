<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\DescriptionDuplicate;

use TheCodingMachine\GraphQLite\Annotations\Query;

class DuplicateController
{
    #[Query]
    public function book(): Book
    {
        return new Book();
    }
}
