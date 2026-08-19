<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\StripFieldPrefixesInput;

use TheCodingMachine\GraphQLite\Annotations\Mutation;

class StockController
{
    #[Mutation]
    public function adjustStock(StockAdjustment $adjustment): int
    {
        return $adjustment->getDelta();
    }
}
