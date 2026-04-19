<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Models;

use TheCodingMachine\GraphQLite\Annotations\Type;

#[Type(name: 'ProductTypes')]
enum ProductTypeEnum: string
{
    case FOOD = 'food';
    case NON_FOOD = 'non food';
}
