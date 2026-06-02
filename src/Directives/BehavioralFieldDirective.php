<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Directives;

use GraphQL\Type\Definition\FieldDefinition;
use TheCodingMachine\GraphQLite\Middlewares\FieldHandlerInterface;
use TheCodingMachine\GraphQLite\QueryFieldDescriptor;

/**
 * A {@see FieldDirective} that also runs behavior. The {@see applyToField} hooks run in declaration
 * order, chained ahead of the rest of the field pipe.
 */
interface BehavioralFieldDirective extends FieldDirective
{
    public function applyToField(QueryFieldDescriptor $descriptor, FieldHandlerInterface $next): FieldDefinition|null;
}
