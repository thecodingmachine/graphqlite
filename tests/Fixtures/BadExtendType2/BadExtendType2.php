<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\BadExtendType2;

use Exception;
use TheCodingMachine\GraphQLite\Annotations\ExtendType;

#[ExtendType(class: Exception::class)]
class BadExtendType2
{
}
