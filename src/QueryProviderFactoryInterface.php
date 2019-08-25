<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

/**
 * Class in charge of creating a query factory.
 * You can pass a query provider factory to the SchemaFactory instead of a query factory if the query factory you want to
 * pass requires the "FieldsBuilder" (that is created by the SchemaFactory itself).
 */
interface QueryProviderFactoryInterface
{
    public function create(FactoryContext $context): QueryProviderInterface;
}
