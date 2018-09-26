<?php


namespace TheCodingMachine\GraphQL\Controllers;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\SchemaConfig;
use TheCodingMachine\GraphQL\Controllers\Registry\Registry;

/**
 * A GraphQL schema that takes into constructor argument a QueryProvider.
 *
 * TODO: turn this into a SchemaFactory (cleaner than extending a class)
 */
class Schema extends \GraphQL\Type\Schema
{
    public function __construct(QueryProviderInterface $queryProvider, Registry $registry, SchemaConfig $config = null)
    {
        if ($config === null) {
            $config = SchemaConfig::create();
        }

        $query = new ObjectType([
            'name' => 'Query',
            'fields' => function() use ($queryProvider) {
                return $queryProvider->getQueries();
            }
        ]);
        $mutation = new ObjectType([
            'name' => 'Mutation',
            'fields' => function() use ($queryProvider) {
                return $queryProvider->getMutations();
            }
        ]);

        $config->setQuery($query);
        $config->setMutation($mutation);
        $config->setTypeLoader(function(string $name) use ($registry) {
            return $registry->get($name);
        });

        parent::__construct($config);
    }
}
