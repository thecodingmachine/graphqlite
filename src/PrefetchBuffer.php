<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use function array_key_exists;
use function md5;
use function serialize;

/**
 * A class in charge of holding the fields for a deferred computation.
 */
class PrefetchBuffer
{
    /** @var array<string, array<int, object>> An array of array of buffered, indexed by hash of arguments. */
    private $objects = [];

    /** @var array<string, mixed> An array of prefetch method results, indexed by hash of arguments. */
    private $results = [];

    /**
     * @param array<int,mixed> $arguments The input arguments passed from GraphQL to the field.
     */
    public function register(object $object, array $arguments): void
    {
        $this->objects[$this->computeHash($arguments)][] = $object;
    }

    /**
     * @param array<int,mixed> $arguments The input arguments passed from GraphQL to the field.
     */
    private function computeHash(array $arguments): string
    {
        return md5(serialize($arguments));
    }

    /**
     * @param array<int,mixed> $arguments The input arguments passed from GraphQL to the field.
     *
     * @return array<int, object>
     */
    public function getObjectsByArguments(array $arguments): array
    {
        return $this->objects[$this->computeHash($arguments)] ?? [];
    }

    /**
     * @param array<int,mixed> $arguments The input arguments passed from GraphQL to the field.
     */
    public function purge(array $arguments): void
    {
        unset($this->objects[$this->computeHash($arguments)]);
    }

    /**
     * @param mixed $result
     * @param array<int,mixed> $arguments The input arguments passed from GraphQL to the field.
     */
    public function storeResult($result, array $arguments): void
    {
        $this->results[$this->computeHash($arguments)] = $result;
    }

    /**
     * @param array<int,mixed> $arguments The input arguments passed from GraphQL to the field.
     */
    public function hasResult(array $arguments): bool
    {
        return array_key_exists($this->computeHash($arguments), $this->results);
    }

    /**
     * @param array<int,mixed> $arguments The input arguments passed from GraphQL to the field.
     *
     * @return mixed
     */
    public function getResult(array $arguments)
    {
        return $this->results[$this->computeHash($arguments)];
    }
}
