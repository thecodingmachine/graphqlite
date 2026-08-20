<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\StripFieldPrefixesInput;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Input;

#[Input]
class StockAdjustment
{
    private int $delta = 0;

    #[Field]
    public function assignDelta(int $delta): void
    {
        $this->delta = $delta;
    }

    public function getDelta(): int
    {
        return $this->delta;
    }
}
