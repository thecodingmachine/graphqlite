<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Tests\Fixtures\BadExtendType;

use TheCodingMachine\GraphQLite\Annotations\ExtendType;

#[ExtendType(name: 'TestObjectInput')]
class BadExtendType
{
}
