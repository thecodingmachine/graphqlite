<?php declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Controllers;

use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\Blog;

class BlogController
{
    /** @return Blog[] */
    #[Query]
    public function getBlogs(): array
    {
        return [
            new Blog(1),
            new Blog(2),
        ];
    }
}
