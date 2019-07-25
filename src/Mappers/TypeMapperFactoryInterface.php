<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Mappers;

use TheCodingMachine\GraphQLite\TypeRegistry;

/**
 * Class in charge of creating a type mapper.
 * You can pass a type mapper factory to the SchemaFactory instead of a type mapper if the type mapper you want to
 * pass requires the "recursive type mapper".
 */
interface TypeMapperFactoryInterface
{
    public function create(RecursiveTypeMapperInterface $recursiveTypeMapper, TypeRegistry $typeRegistry): TypeMapperInterface;
}
