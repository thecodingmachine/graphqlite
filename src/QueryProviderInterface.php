<?php


namespace TheCodingMachine\GraphQL\Controllers;

use Youshido\GraphQL\Field\Field;

/**
 * Returns a list of queries to be put in the GraphQL schema
 */
interface QueryProviderInterface
{
    /**
     * @return Field[]
     */
    public function getQueries(): array;

    /**
     * @return Field[]
     */
    public function getMutations(): array;
}
