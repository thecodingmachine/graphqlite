<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Middlewares;

use GraphQL\Type\Definition\FieldDefinition;
use TheCodingMachine\GraphQLite\QueryFieldDescriptor;

interface FieldHandlerInterface
{
    /**
     * Handles a field descriptor and produces a field.
     *
     * May call other collaborating code to generate the field.
     */
    public function handle(QueryFieldDescriptor $fieldDescriptor): ?FieldDefinition;
}
