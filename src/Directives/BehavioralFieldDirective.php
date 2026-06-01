<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Directives;

use GraphQL\Type\Definition\FieldDefinition;
use TheCodingMachine\GraphQLite\Middlewares\FieldHandlerInterface;
use TheCodingMachine\GraphQLite\QueryFieldDescriptor;

/**
 * A {@see FieldDirective} that also has PHP-side behavior. Each behavioral directive's
 * {@see applyToField} runs in declaration order as a sub-chain leading into the outer field pipe,
 * with the standard `(descriptor, next)` middleware shape.
 */
interface BehavioralFieldDirective extends FieldDirective
{
    public function applyToField(QueryFieldDescriptor $descriptor, FieldHandlerInterface $next): FieldDefinition|null;
}
