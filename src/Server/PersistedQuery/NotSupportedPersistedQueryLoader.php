<?php

namespace TheCodingMachine\GraphQLite\Server\PersistedQuery;

use GraphQL\Language\AST\DocumentNode;
use GraphQL\Server\OperationParams;
use GraphQL\Server\ServerConfig;

/**
 * Simply reports all attempts to load a persisted query as not supported so that clients don't continuously attempt to load them.
 *
 * @phpstan-import-type PersistedQueryLoader from ServerConfig
 *
 * @implements PersistedQueryLoader
 */
class NotSupportedPersistedQueryLoader
{
    public function __invoke(string $queryId, OperationParams $operation): string|DocumentNode
    {
        throw new PersistedQueryNotSupportedException();
    }
}