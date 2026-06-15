<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Directives;

use TheCodingMachine\GraphQLite\Middlewares\ObjectTypeHandlerInterface;
use TheCodingMachine\GraphQLite\ObjectTypeDescriptor;
use TheCodingMachine\GraphQLite\Types\MutableObjectType;

/**
 * An {@see ObjectTypeDirective} that also runs behavior, usually tweaking the built
 * {@see MutableObjectType}. Dispatched through the object-type pipe in
 * {@see \TheCodingMachine\GraphQLite\TypeGenerator}.
 */
interface BehavioralObjectTypeDirective extends ObjectTypeDirective
{
    public function applyToObjectType(ObjectTypeDescriptor $descriptor, ObjectTypeHandlerInterface $next): MutableObjectType;
}
