<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;

interface GlobControllerQueryProviderFactoryInterface
{
    /**
     * @param string $namespace
     * @param FieldsBuilder $fieldsBuilder
     * @param ContainerInterface $container
     * @param CacheInterface $cache
     * @param int|null $cacheTtl
     * @param bool $recursive
     * @return QueryProviderInterface
     */
    public function create(
        string $namespace,
        FieldsBuilder $fieldsBuilder,
        ContainerInterface $container,
        CacheInterface $cache,
        ?int $cacheTtl = null,
        bool $recursive = true
    ): QueryProviderInterface;
}
