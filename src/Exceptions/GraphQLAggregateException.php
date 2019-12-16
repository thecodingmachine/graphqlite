<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Exceptions;

use Exception;
use GraphQL\Error\ClientAware;
use Throwable;
use function array_map;
use function assert;
use function count;
use function max;
use function reset;

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
        $this->updateCode();
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

    /**
     * By convention, the aggregated code is the highest code of all exceptions
     */
    private function updateCode(): void
    {
        $codes = array_map(static function (Throwable $t) {
            return $t->getCode();
        }, $this->exceptions);
        $this->code = max($codes);
    }

    /**
     * Throw the exceptions passed in parameter.
     * If only one exception is passed, it is thrown.
     * If many exceptions are passed, they are bundled in the GraphQLAggregateException
     *
     * @param (ClientAware&Throwable)[] $exceptions
     */
    public static function throwExceptions(array $exceptions): void
    {
        $count = count($exceptions);
        if ($count === 0) {
            return;
        }
        if ($count === 1) {
            $exception = reset($exceptions);
            assert($exception instanceof Throwable);
            throw $exception;
        }
        throw new self($exceptions);
    }
}
