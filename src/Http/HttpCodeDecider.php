<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Http;

use GraphQL\Error\ClientAware;
use GraphQL\Executor\ExecutionResult;
use function max;

class HttpCodeDecider implements HttpCodeDeciderInterface
{
    /**
     * Decides the HTTP status code based on the answer.
     *
     * @see https://github.com/APIs-guru/graphql-over-http#status-codes
     */
    public function decideHttpStatusCode(ExecutionResult $result): int
    {
        // If the data entry in the response has any value other than null (when the operation has successfully executed without error) then the response should use the 200 (OK) status code.
        if ($result->data !== null && empty($result->errors)) {
            return 200;
        }

        $status = 0;
        // There might be many errors. Let's return the highest code we encounter.
        foreach ($result->errors as $error) {
            $wrappedException = $error->getPrevious();
            if ($wrappedException !== null) {
                $code = $wrappedException->getCode();
                if ($code < 400 || $code >= 600) {
                    if (! ($wrappedException instanceof ClientAware) || $wrappedException->isClientSafe() !== true) {
                        // The exception code is not a valid HTTP code. Let's ignore it
                        continue;
                    }

                    // A "client aware" exception is almost certainly targeting the client (there is
                    // no need to pass a server exception error message to the client).
                    // So a ClientAware exception is almost certainly a HTTP 400 code
                    $code = 400;
                }
            } else {
                $code = 400;
            }
            $status = max($status, $code);
        }

        // If exceptions have been thrown and they have not a "HTTP like code", let's throw a 500.
        if ($status < 200) {
            $status = 500;
        }

        return $status;
    }
}
