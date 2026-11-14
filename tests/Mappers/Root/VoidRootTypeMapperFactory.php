<?php


namespace TheCodingMachine\GraphQLite\Tests\Mappers\Root;

use TheCodingMachine\GraphQLite\Mappers\Root\RootTypeMapperFactoryContext;
use TheCodingMachine\GraphQLite\Mappers\Root\RootTypeMapperFactoryInterface;
use TheCodingMachine\GraphQLite\Mappers\Root\RootTypeMapperInterface;


class VoidRootTypeMapperFactory implements RootTypeMapperFactoryInterface
{
    public function create(RootTypeMapperInterface $next, RootTypeMapperFactoryContext $context): RootTypeMapperInterface
    {
        return new VoidRootTypeMapper($next);
    }
}