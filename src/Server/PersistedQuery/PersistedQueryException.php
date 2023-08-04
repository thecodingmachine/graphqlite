<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Server\PersistedQuery;

use TheCodingMachine\GraphQLite\Exceptions\GraphQLExceptionInterface;
use Throwable;

interface PersistedQueryException extends Throwable, GraphQLExceptionInterface
{
}
