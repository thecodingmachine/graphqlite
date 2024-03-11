<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Models;

use TheCodingMachine\GraphQLite\Annotations\Type;

#[Type]
class Company
{
    public function __construct(
         public readonly string $name
    ) {
    }
}
