<?php


namespace TheCodingMachine\GraphQLite;


/**
 * Returns a list of queries to be put in the GraphQL schema
 */
interface QueryProviderInterface
{
    /**
     * @return QueryField[]
     */
    public function getQueries(): array;

    /**
     * @return QueryField[]
     */
    public function getMutations(): array;
}
