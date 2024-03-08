<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures;

use TheCodingMachine\GraphQLite\Annotations as GraphQLite;

#[GraphQLite\Input]
class PropertyPromotionInputTypeWithoutGenericDoc
{

    /**
     * We expect this to fail since the array must have a generic type `array<int>`
     */
    public function __construct(
        #[GraphQLite\Field]
        public array $amounts,
    ) {}
}
