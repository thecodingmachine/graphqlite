<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Directives;

use TheCodingMachine\GraphQLite\InputObjectTypeDescriptor;
use TheCodingMachine\GraphQLite\Middlewares\InputObjectTypeHandlerInterface;
use TheCodingMachine\GraphQLite\Types\MutableInputObjectType;

/**
 * An {@see InputObjectTypeDirective} that also runs behavior, e.g. `@oneOf` flipping a flag on the
 * built input type.
 */
interface BehavioralInputObjectTypeDirective extends InputObjectTypeDirective
{
    public function applyToInputObjectType(InputObjectTypeDescriptor $descriptor, InputObjectTypeHandlerInterface $next): MutableInputObjectType;
}
