<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;

class GlobControllerQueryProviderFactory implements GlobControllerQueryProviderFactoryInterface
{

    /**
     * @inheritDoc
     */
    public function create(
        string $namespace,
        FieldsBuilder $fieldsBuilder,
        ContainerInterface $container,
        CacheInterface $cache,
        ?int $cacheTtl = null,
        bool $recursive = true
    ): QueryProviderInterface
    {
        return new GlobControllerQueryProvider(
            $namespace,
            $fieldsBuilder,
            $container,
            $cache,
            $cacheTtl,
            $recursive
        );
    }
}
