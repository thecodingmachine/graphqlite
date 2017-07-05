<?php


namespace TheCodingMachine\GraphQL\Controllers;


use Youshido\GraphQL\Config\Schema\SchemaConfig;
use Youshido\GraphQL\Schema\AbstractSchema;

/**
 * A GraphQL schema that takes into constructor argument a QueryProvider.
 *
 * Note: does not support mutators yet.
 */
class Schema extends AbstractSchema
{
    /**
     * @var QueryProviderInterface
     */
    private $queryProvider;

    public function __construct(QueryProviderInterface $queryProvider, array $config = [])
    {
        $this->queryProvider = $queryProvider;
        parent::__construct($config);
    }

    public function build(SchemaConfig $config)
    {
        $config->getQuery()->addFields($this->queryProvider->getQueries());
    }
}
