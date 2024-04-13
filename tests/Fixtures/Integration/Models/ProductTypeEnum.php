<?php

namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Models;

use MyCLabs\Enum\Enum;
use TheCodingMachine\GraphQLite\Annotations\EnumType;
#[EnumType(name: "ProductTypes")]
class ProductTypeEnum extends Enum
{
    const FOOD = 'food';
    const NON_FOOD = 'non food';
}
