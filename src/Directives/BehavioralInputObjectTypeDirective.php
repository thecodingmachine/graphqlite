<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Directives;

use TheCodingMachine\GraphQLite\InputObjectTypeDescriptor;
use TheCodingMachine\GraphQLite\Middlewares\InputObjectTypeHandlerInterface;
use TheCodingMachine\GraphQLite\Types\MutableInputObjectType;

/**
 * An {@see InputObjectTypeDirective} that also has PHP-side behavior. Used for things like
 * `@oneOf` that flip a flag on the built input type.
 */
interface BehavioralInputObjectTypeDirective extends InputObjectTypeDirective
{
    public function applyToInputObjectType(InputObjectTypeDescriptor $descriptor, InputObjectTypeHandlerInterface $next): MutableInputObjectType;
}
