<?php


namespace TheCodingMachine\GraphQLite\Mappers\Root;


class VoidRootTypeMapperFactory implements RootTypeMapperFactoryInterface
{
    public function create(RootTypeMapperInterface $next, RootTypeMapperFactoryContext $context): RootTypeMapperInterface
    {
        return new VoidRootTypeMapper($next);
    }
}