<?php

namespace TheCodingMachine\GraphQLite\Server\PersistedQuery;

use GraphQL\Error\ClientAware;
use RuntimeException;
use TheCodingMachine\GraphQLite\Exceptions\GraphQLExceptionInterface;
use Throwable;

interface PersistedQueryException extends Throwable, GraphQLExceptionInterface
{
}
