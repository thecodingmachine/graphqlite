<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Tests\Fixtures\BadClassType;

use TheCodingMachine\GraphQLite\Annotations\Type;

#[Type(class: 'Foobar')]
class TestType
{
}
