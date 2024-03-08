<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use GraphQL\Type\Definition\ResolveInfo;
use function array_key_exists;
use function md5;
use function serialize;

/**
 * A class in charge of holding the fields for a deferred computation.
 */
class PrefetchBuffer
{
    /** @var array<string, array<int, object>> An array of buffered, indexed by hash of arguments. */
    private array $objects = [];

    /** @var array<string, mixed> An array of prefetch method results, indexed by hash of arguments. */
    private array $results = [];

    /** @param array<int,mixed> $arguments The input arguments passed from GraphQL to the field. */
    public function register(
        object $object,
        array $arguments,
        ?ResolveInfo $info = null
    ): void {
        $this->objects[$this->computeHash($arguments, $info)][] = $object;
    }

    /** @param array<int,mixed> $arguments The input arguments passed from GraphQL to the field. */
    private function computeHash(
        array $arguments,
        ?ResolveInfo $info = null
    ): string {
        if (
            null === $info
            || null === ($queryBody = $info->operation->loc?->source?->body)
        ) {
            return md5(serialize($arguments));
        }
        return md5(serialize($arguments) . $queryBody);
    }

    /**
     * @param array<int,mixed> $arguments The input arguments passed from GraphQL to the field.
     *
     * @return array<int, object>
     */
    public function getObjectsByArguments(
        array $arguments,
        ?ResolveInfo $info = null
    ): array {
        return $this->objects[$this->computeHash($arguments, $info)] ?? [];
    }

    /** @param array<int,mixed> $arguments The input arguments passed from GraphQL to the field. */
    public function purge(
        array $arguments,
        ?ResolveInfo $info = null
    ): void {
        unset($this->objects[$this->computeHash($arguments, $info)]);
    }

    /** @param array<int,mixed> $arguments The input arguments passed from GraphQL to the field. */
    public function storeResult(
        mixed $result,
        array $arguments,
        ?ResolveInfo $info = null
    ): void {
        $this->results[$this->computeHash($arguments, $info)] = $result;
    }

    /** @param array<int,mixed> $arguments The input arguments passed from GraphQL to the field. */
    public function hasResult(
        array $arguments,
        ?ResolveInfo $info = null
    ): bool {
        return array_key_exists($this->computeHash($arguments, $info), $this->results);
    }

    /** @param array<int,mixed> $arguments The input arguments passed from GraphQL to the field. */
    public function getResult(
        array $arguments,
        ?ResolveInfo $info = null
    ): mixed {
        return $this->results[$this->computeHash($arguments, $info)];
    }
}
