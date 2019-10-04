<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Exceptions;

use GraphQL\Error\ClientAware;
use Throwable;

/**
 * Exceptions implementing this interface are caught by GraphQLite and displayed as "errors" in the GraphQL response.
 */
interface GraphQLExceptionInterface extends ClientAware, Throwable
{
    /**
     * Returns the "extensions" object attached to the GraphQL error.
     *
     * @return array<string, mixed>
     */
    public function getExtensions(): array;
}
