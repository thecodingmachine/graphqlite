<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures80;

use TheCodingMachine\GraphQLite\Annotations as GraphQLite;

#[GraphQLite\Input]
class PropertyPromotionInputType
{

    /**
     * Constructor
     *
     * @param array<int> $amounts
     */
    public function __construct(
        #[GraphQLite\Field]
        public array $amounts,
    ) {}
}
