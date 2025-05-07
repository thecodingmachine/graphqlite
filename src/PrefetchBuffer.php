<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use GraphQL\Type\Definition\ResolveInfo;
use WeakMap;

use function md5;
use function serialize;

/**
 * A class in charge of holding the fields for a deferred computation.
 */
class PrefetchBuffer
{
    /** @var array<string, array<int, object>> An array of buffered, indexed by hash of arguments. */
    private array $objects = [];

    /** @var WeakMap A Storage of prefetch method results, holds source to resolved values. */
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

    public function storeResult(
        object $source,
        mixed $result,
    ): void {
        $this->results->offsetSet($source, $result);
    }

    public function hasResult(
        object $source,
    ): bool {
        return $this->results->offsetExists($source);
    }

    public function getResult(
        object $source,
    ): mixed {
        return $this->results->offsetGet($source);
    }

    public function purgeResult(
        object $source,
    ): void {
        $this->results->offsetUnset($source);
    }
}
