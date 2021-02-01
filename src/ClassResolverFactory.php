<?php


namespace TheCodingMachine\GraphQLite;


use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;

class ClassResolverFactory
{
    public function create(ContainerInterface $container, CacheInterface $cache) : ClassResolver
    {
        return new ClassResolverCache($cache, new ClassResolverFromContainer($container));
    }
}