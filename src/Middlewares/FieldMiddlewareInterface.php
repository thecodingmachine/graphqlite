<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Middlewares;

use GraphQL\Type\Definition\FieldDefinition;
use TheCodingMachine\GraphQLite\QueryFieldDescriptor;

/**
 * A middleware use to process annotations when evaluating a field/query/mutation
 *
 * @unstable See https://graphqlite.thecodingmachine.io/docs/semver.html
 */
interface FieldMiddlewareInterface
{
    public function process(QueryFieldDescriptor $queryFieldDescriptor, FieldHandlerInterface $fieldHandler): ?FieldDefinition;
}
