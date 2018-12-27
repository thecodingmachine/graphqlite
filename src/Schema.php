<?php


namespace TheCodingMachine\GraphQL\Controllers;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\SchemaConfig;
use TheCodingMachine\GraphQL\Controllers\Containers\BasicAutoWiringContainer;
use TheCodingMachine\GraphQL\Controllers\Mappers\RecursiveTypeMapperInterface;
use TheCodingMachine\GraphQL\Controllers\Types\CustomTypesRegistry;
use TheCodingMachine\GraphQL\Controllers\Types\TypeResolver;

/**
 * A GraphQL schema that takes into constructor argument a QueryProvider.
 *
 * TODO: turn this into a SchemaFactory (cleaner than extending a class)
 */
class Schema extends \GraphQL\Type\Schema
{
    public function __construct(QueryProviderInterface $queryProvider, RecursiveTypeMapperInterface $recursiveTypeMapper, TypeResolver $typeResolver, SchemaConfig $config = null)
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

        $config->setTypeLoader(function(string $name) use ($recursiveTypeMapper, $query, $mutation) {
            // We need to find a type FROM a GraphQL type name
            if ($name === 'Query') {
                return $query;
            }
            if ($name === 'Mutation') {
                return $mutation;
            }

            $type = CustomTypesRegistry::mapNameToType($name);
            if ($type !== null) {
                return $type;
            }

            return $recursiveTypeMapper->mapNameToType($name);
        });

        $typeResolver->registerSchema($this);

        parent::__construct($config);
    }
}
