<?php


namespace TheCodingMachine\GraphQL\Controllers;

use Youshido\GraphQL\Field\FieldInterface;

/**
 * Returns a list of queries to be put in the GraphQL schema
 */
interface QueryProviderInterface
{
    /**
     * @return FieldInterface[]
     */
    public function getQueries(): array;

    /**
     * @return FieldInterface[]
     */
    public function getMutations(): array;
}
