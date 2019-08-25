<?php

namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Models;

use MyCLabs\Enum\Enum;

class ProductTypeEnum extends Enum
{
    const FOOD = 'food';
    const NON_FOOD = 'non food';
}
