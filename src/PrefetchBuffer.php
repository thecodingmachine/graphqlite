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
    public function register(object $object, string $prefetchMethodName, array $arguments): void
    {
        $this->objects[$this->computeHash($prefetchMethodName, $arguments)][] = $object;
    }

    /**
     * @param array<int,mixed> $arguments The input arguments passed from GraphQL to the field.
     */
    private function computeHash(string $prefetchMethodName, array $arguments): string
    {
        return md5(serialize($arguments) . $prefetchMethodName);
    }

    /**
     * @param array<int,mixed> $arguments The input arguments passed from GraphQL to the field.
     *
     * @return array<int, object>
     */
    public function getObjectsByArguments(string $prefetchMethodName, array $arguments): array
    {
        return $this->objects[$this->computeHash($prefetchMethodName, $arguments)] ?? [];
    }

    /**
     * @param array<int,mixed> $arguments The input arguments passed from GraphQL to the field.
     */
    public function purge($prefetchMethodName, array $arguments): void
    {
        unset($this->objects[$this->computeHash($prefetchMethodName, $arguments)]);
    }

    /**
     * @param mixed $result
     * @param array<int,mixed> $arguments The input arguments passed from GraphQL to the field.
     */
    public function storeResult($result, string $prefetchMethodName, array $arguments): void
    {
        $this->results[$this->computeHash($prefetchMethodName, $arguments)] = $result;
    }

    /**
     * @param array<int,mixed> $arguments The input arguments passed from GraphQL to the field.
     */
    public function hasResult(string $prefetchMethodName, array $arguments): bool
    {
        return array_key_exists($this->computeHash($prefetchMethodName, $arguments), $this->results);
    }

    /**
     * @param array<int,mixed> $arguments The input arguments passed from GraphQL to the field.
     *
     * @return mixed
     */
    public function getResult(string $prefetchMethodName, array $arguments)
    {
        return $this->results[$this->computeHash($prefetchMethodName, $arguments)];
    }
}
