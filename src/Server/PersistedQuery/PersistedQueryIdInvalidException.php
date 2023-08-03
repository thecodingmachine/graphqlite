<?php

namespace TheCodingMachine\GraphQLite\Server\PersistedQuery;

use GraphQL\Server\RequestError;
use Throwable;

/**
 * This isn't part of an Apollo spec, but it's still nice to have.
 */
class PersistedQueryIdInvalidException extends RequestError implements PersistedQueryException
{
    public function __construct(Throwable $previous = null) {
        parent::__construct('Persisted query by that ID doesnt match the provided query; you are likely incorrectly hashing your query.', previous: $previous);

        $this->code = 'PERSISTED_QUERY_ID_INVALID';
    }

    public function getExtensions(): array
    {
        return [
            'code' => $this->code,
        ];
    }

    public function isClientSafe(): bool
    {
        return true;
    }
}