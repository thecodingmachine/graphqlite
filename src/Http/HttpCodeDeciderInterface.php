<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Http;

use GraphQL\Executor\ExecutionResult;

interface HttpCodeDeciderInterface
{
    /**
     * Decides the HTTP status code based on the answer.
     *
     * @see https://github.com/APIs-guru/graphql-over-http#status-codes
     */
    public function decideHttpStatusCode(ExecutionResult $result): int;
}
