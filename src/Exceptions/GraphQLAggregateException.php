<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Exceptions;

use Exception;
use GraphQL\Error\ClientAware;
use Throwable;

class GraphQLAggregateException extends Exception implements GraphQLAggregateExceptionInterface
{
    /** @var (ClientAware&Throwable)[] */
    private $exceptions = [];

    /**
     * @param (ClientAware&Throwable)[] $exceptions
     */
    public function __construct(iterable $exceptions = [])
    {
        parent::__construct('Many exceptions have be thrown:');
        foreach ($exceptions as $exception) {
            $this->add($exception);
        }
    }

    /**
     * @param ClientAware&Throwable $exception
     */
    public function add(ClientAware $exception): void
    {
        $this->exceptions[] = $exception;
        $this->message .= "\n" . $exception->getMessage();
    }

    /**
     * @return (ClientAware&Throwable)[]
     */
    public function getExceptions(): array
    {
        return $this->exceptions;
    }

    public function hasExceptions(): bool
    {
        return ! empty($this->exceptions);
    }
}
