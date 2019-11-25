<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Mappers\Root;

/**
 * Class in charge of creating a root type mapper.
 * Since root type mappers are chained, the "create" function passes the next root type mapper down the chain
 */
interface RootTypeMapperFactoryInterface
{
    public function create(RootTypeMapperInterface $next, RootTypeMapperFactoryContext $context): RootTypeMapperInterface;
}
