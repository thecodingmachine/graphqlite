<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Http;

use GraphQL\Executor\ExecutionResult;

interface HttpCodeDeciderInterface
{
    /**
     * Decides the HTTP status code based on the answer.
     *
     * @see https://github.com/graphql/graphql-over-http/blob/main/spec/GraphQLOverHTTP.md#status-codes
     */
    public function decideHttpStatusCode(ExecutionResult $result): int;
}
