<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Exceptions;

use Exception;
use Throwable;

class GraphQLException extends Exception implements GraphQLExceptionInterface
{
    /** @param array<string, mixed> $extensions */
    public function __construct(
        string $message,
        int $code = 0,
        Throwable|null $previous = null,
        protected array $extensions = [],
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Returns true when exception message is safe to be displayed to a client.
     */
    public function isClientSafe(): bool
    {
        return true;
    }

    /**
     * Returns the "extensions" object attached to the GraphQL error.
     *
     * @return array<string, mixed>
     */
    public function getExtensions(): array
    {
        return $this->extensions;
    }
}
