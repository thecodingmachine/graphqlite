<?php


namespace TheCodingMachine\GraphQL\Controllers;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\SchemaConfig;
use TheCodingMachine\GraphQL\Controllers\Containers\BasicAutoWiringContainer;
use TheCodingMachine\GraphQL\Controllers\Mappers\RecursiveTypeMapperInterface;

/**
 * A GraphQL schema that takes into constructor argument a QueryProvider.
 *
 * TODO: turn this into a SchemaFactory (cleaner than extending a class)
 */
class Schema extends \GraphQL\Type\Schema
{
    public function __construct(QueryProviderInterface $queryProvider, RecursiveTypeMapperInterface $recursiveTypeMapper, SchemaConfig $config = null)
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

        $config->setTypes(function() use ($recursiveTypeMapper) {
            return $recursiveTypeMapper->getOutputTypes();
        });

        $config->setTypeLoader(function(string $name) use ($recursiveTypeMapper) {
            // We need to find a type FROM a GraphQL type name
            return $recursiveTypeMapper->mapNameToType($name);
        });

        parent::__construct($config);
    }
}
