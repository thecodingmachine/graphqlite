<?php

namespace TheCodingMachine\GraphQLite\Server\PersistedQuery;

use GraphQL\Error\Error;
use Throwable;

/**
 * See https://github.com/apollographql/apollo-client/blob/fc450f227522c5311375a6b59ec767ac45f151c7/src/link/persisted-queries/index.ts#L73
 */
class PersistedQueryNotFoundException extends Error implements PersistedQueryException
{
    public function __construct(Throwable $previous = null) {
        parent::__construct('Persisted query by that ID was not found and "query" was omitted.', previous: $previous);

        $this->code = 'PERSISTED_QUERY_NOT_FOUND';
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