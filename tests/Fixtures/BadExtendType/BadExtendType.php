<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\BadExtendType;

use TheCodingMachine\GraphQLite\Annotations\ExtendType;

#[ExtendType(name: 'TestObjectInput')]
class BadExtendType
{
}
