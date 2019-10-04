<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Exceptions;

use GraphQL\Error\ClientAware;
use Throwable;

/**
 * Exceptions implementing this interface can aggregate many GraphQL exceptions together.
 * Use this is you want to return more than one GraphQL error by throwing only one exception.
 */
interface GraphQLAggregateExceptionInterface extends Throwable
{
    /**
     * @return (ClientAware&Throwable)[]
     */
    public function getExceptions(): array;
}
