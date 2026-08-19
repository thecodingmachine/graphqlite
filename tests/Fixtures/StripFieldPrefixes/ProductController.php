<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\StripFieldPrefixes;

use TheCodingMachine\GraphQLite\Annotations\Query;

class ProductController
{
    #[Query]
    public function product(): Product
    {
        return new Product('Widget', true);
    }
}
