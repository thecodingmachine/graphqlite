<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\SchemaConfig;
use TheCodingMachine\GraphQLite\Mappers\RecursiveTypeMapperInterface;
use TheCodingMachine\GraphQLite\Mappers\Root\RootTypeMapperInterface;
use TheCodingMachine\GraphQLite\Types\TypeResolver;

/**
 * A GraphQL schema that takes into constructor argument a QueryProvider.
 *
 * TODO: turn this into a SchemaFactory (cleaner than extending a class)
 */
class Schema extends \GraphQL\Type\Schema
{
    public function __construct(
        QueryProviderInterface $queryProvider,
        RecursiveTypeMapperInterface $recursiveTypeMapper,
        TypeResolver $typeResolver,
        RootTypeMapperInterface $rootTypeMapper,
        SchemaConfig|null $config = null,
    ) {
        if ($config === null) {
            $config = SchemaConfig::create();
        }

        $query = new ObjectType([
            'name' => 'Query',
            'fields' => static function () use ($queryProvider) {
                $queries = $queryProvider->getQueries();
                if (empty($queries)) {
                    return [
                        'dummyQuery' => [
                            'type' => Type::string(),
                            'description' => 'A placeholder query used by thecodingmachine/graphqlite when there are no declared queries.',
                            'resolve' => static function () {
                                return 'This is a placeholder query. Please create a query using the "Query" attribute.';
                            },
                        ],
                    ];
                }

                return $queries;
            },
        ]);

        $mutation = new ObjectType([
            'name' => 'Mutation',
            'fields' => static function () use ($queryProvider) {
                $mutations = $queryProvider->getMutations();
                if (empty($mutations)) {
                    return [
                        'dummyMutation' => [
                            'type' => Type::string(),
                            'description' => 'A placeholder query used by thecodingmachine/graphqlite when there are no declared mutations.',
                            'resolve' => static function () {
                                return 'This is a placeholder mutation. Please create a mutation using the "Mutation" attribute.';
                            },
                        ],
                    ];
                }

                return $mutations;
            },
        ]);

        $subscription = new ObjectType([
            'name' => 'Subscription',
            'fields' => static function () use ($queryProvider) {
                $subscriptions = $queryProvider->getSubscriptions();
                if (empty($subscriptions)) {
                    return [
                        'dummySubscription' => [
                            'type' => Type::string(),
                            'description' => 'A placeholder query used by thecodingmachine/graphqlite when there are no declared subscriptions.',
                            'resolve' => static function () {
                                return 'This is a placeholder subscription. Please create a subscription using the "Subscription" attribute.';
                            },
                        ],
                    ];
                }

                return $subscriptions;
            },
        ]);

        $config->setQuery($query);
        $config->setMutation($mutation);
        $config->setSubscription($subscription);

        $config->setTypes(static function () use ($recursiveTypeMapper) {
            return $recursiveTypeMapper->getOutputTypes();
        });

        $config->setTypeLoader(static function (string $name) use ($query, $mutation, $subscription, $rootTypeMapper) {
            // We need to find a type FROM a GraphQL type name
            if ($name === 'Query') {
                return $query;
            }
            if ($name === 'Mutation') {
                return $mutation;
            }

            if ($name === 'Subscription') {
                return $subscription;
            }

            return $rootTypeMapper->mapNameToType($name);
        });

        $typeResolver->registerSchema($this);

        parent::__construct($config);
    }
}
