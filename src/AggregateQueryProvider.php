<?php


namespace TheCodingMachine\GraphQL\Controllers;

use function array_map;
use function array_merge;

/**
 * A query provider that aggregates several query providers together.
 */
class AggregateQueryProvider implements QueryProviderInterface
{
    /**
     * @var QueryProviderInterface[]
     */
    private $queryProviders;

    /**
     * @param QueryProviderInterface[] $queryProviders
     */
    public function __construct(array $queryProviders)
    {
        $this->queryProviders = $queryProviders;
    }

    /**
     * @return QueryField[]
     */
    public function getQueries(): array
    {
        $queriesArray = array_map(function(QueryProviderInterface $queryProvider) { return $queryProvider->getQueries(); }, $this->queryProviders);
        if ($queriesArray === []) {
            return [];
        }
        return array_merge(...$queriesArray);
    }

    /**
     * @return QueryField[]
     */
    public function getMutations(): array
    {
        $mutationsArray = array_map(function(QueryProviderInterface $queryProvider) { return $queryProvider->getMutations(); }, $this->queryProviders);
        if ($mutationsArray === []) {
            return [];
        }
        return array_merge(...$mutationsArray);
    }
}
