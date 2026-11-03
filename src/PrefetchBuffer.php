<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use GraphQL\Type\Definition\ResolveInfo;
use WeakMap;

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

    /**
     * @var WeakMap<object, array<string, mixed>> Prefetch method results per source, indexed by hash of arguments, so
     * aliases of one field with different arguments don't share a result.
     */
    private WeakMap $results;

    public function __construct()
    {
        $this->results = new WeakMap();
    }

    /** @param array<array-key, mixed> $arguments The input arguments passed from GraphQL to the field. */
    public function register(
        object $object,
        array $arguments,
        ResolveInfo|null $info = null,
    ): void {
        $this->objects[$this->computeHash($arguments, $info)][] = $object;
    }

    /** @param array<array-key, mixed> $arguments The input arguments passed from GraphQL to the field. */
    private function computeHash(
        array $arguments,
        ResolveInfo|null $info,
    ): string {
        if (
            $info instanceof ResolveInfo
            && isset($info->operation)
            && $info->operation->loc?->source?->body !== null
        ) {
            return md5(serialize($arguments) . $info->operation->loc->source->body);
        }

        return md5(serialize($arguments));
    }

    /**
     * @param array<array-key, mixed> $arguments The input arguments passed from GraphQL to the field.
     *
     * @return array<int, object>
     */
    public function getObjectsByArguments(
        array $arguments,
        ResolveInfo|null $info = null,
    ): array {
        return $this->objects[$this->computeHash($arguments, $info)] ?? [];
    }

    /** @param array<array-key, mixed> $arguments The input arguments passed from GraphQL to the field. */
    public function purge(
        array $arguments,
        ResolveInfo|null $info = null,
    ): void {
        unset($this->objects[$this->computeHash($arguments, $info)]);
    }

    /** @param array<array-key, mixed> $arguments The input arguments passed from GraphQL to the field. */
    public function storeResult(
        object $source,
        mixed $result,
        array $arguments = [],
        ResolveInfo|null $info = null,
    ): void {
        $results = $this->results[$source] ?? [];
        $results[$this->computeHash($arguments, $info)] = $result;
        $this->results[$source] = $results;
    }

    /** @param array<array-key, mixed> $arguments The input arguments passed from GraphQL to the field. */
    public function hasResult(
        object $source,
        array $arguments = [],
        ResolveInfo|null $info = null,
    ): bool {
        return array_key_exists($this->computeHash($arguments, $info), $this->results[$source] ?? []);
    }

    /** @param array<array-key, mixed> $arguments The input arguments passed from GraphQL to the field. */
    public function getResult(
        object $source,
        array $arguments = [],
        ResolveInfo|null $info = null,
    ): mixed {
        return $this->results[$source][$this->computeHash($arguments, $info)];
    }

    /** @param array<array-key, mixed> $arguments The input arguments passed from GraphQL to the field. */
    public function purgeResult(
        object $source,
        array $arguments = [],
        ResolveInfo|null $info = null,
    ): void {
        $results = $this->results[$source] ?? [];
        unset($results[$this->computeHash($arguments, $info)]);

        if ($results === []) {
            unset($this->results[$source]);
        } else {
            $this->results[$source] = $results;
        }
    }
}
