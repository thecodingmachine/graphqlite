<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use function array_map;
use function array_merge;
use function is_array;
use function iterator_to_array;

/**
 * A query provider that aggregates several query providers together.
 */
class AggregateQueryProvider implements QueryProviderInterface
{
    /** @var QueryProviderInterface[] */
    private array $queryProviders;

    /** @param QueryProviderInterface[] $queryProviders */
    public function __construct(iterable $queryProviders)
    {
        $this->queryProviders = is_array($queryProviders)
            ? $queryProviders
            : iterator_to_array($queryProviders);
    }

    /** @return QueryField[] */
    public function getQueries(): array
    {
        $queriesArray = array_map(static function (QueryProviderInterface $queryProvider) {
            return $queryProvider->getQueries();
        }, $this->queryProviders);
        if ($queriesArray === []) {
            return [];
        }

        return array_merge(...$queriesArray);
    }

    /** @return QueryField[] */
    public function getMutations(): array
    {
        $mutationsArray = array_map(static function (QueryProviderInterface $queryProvider) {
            return $queryProvider->getMutations();
        }, $this->queryProviders);
        if ($mutationsArray === []) {
            return [];
        }

        return array_merge(...$mutationsArray);
    }

    /** @return QueryField[] */
    public function getSubscriptions(): array
    {
        $subscriptionsArray = array_map(static function (QueryProviderInterface $queryProvider) {
            return $queryProvider->getSubscriptions();
        }, $this->queryProviders);
        if ($subscriptionsArray === []) {
            return [];
        }

        return array_merge(...$subscriptionsArray);
    }
}
