<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Middlewares;

use Exception;
use GraphQL\Error\ClientAware;

class MissingAuthorizationException extends Exception implements ClientAware
{
    public static function unauthorized(): self
    {
        return new self('You need to be logged to access this field', 401);
    }

    public static function forbidden(): self
    {
        return new self('You do not have sufficient rights to access this field', 403);
    }

    /**
     * Returns true when exception message is safe to be displayed to a client.
     */
    public function isClientSafe(): bool
    {
        return true;
    }
}
