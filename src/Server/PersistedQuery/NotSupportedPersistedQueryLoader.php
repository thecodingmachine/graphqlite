<?php

namespace TheCodingMachine\GraphQLite\Server\PersistedQuery;

use GraphQL\Language\AST\DocumentNode;
use GraphQL\Server\OperationParams;

/**
 * Simply reports all attempts to load a persisted query as not supported so that clients don't continuously attempt to load them.
 */
class NotSupportedPersistedQueryLoader
{
    public function __invoke(string $queryId, OperationParams $operation): string|DocumentNode
    {
        throw new PersistedQueryNotSupportedException();
    }
}