<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Directives;

use TheCodingMachine\GraphQLite\InputField;
use TheCodingMachine\GraphQLite\InputFieldDescriptor;
use TheCodingMachine\GraphQLite\Middlewares\InputFieldHandlerInterface;

/**
 * An {@see InputFieldDirective} that also has PHP-side behavior. Dispatched through the input-field
 * pipe.
 */
interface BehavioralInputFieldDirective extends InputFieldDirective
{
    public function applyToInputField(InputFieldDescriptor $descriptor, InputFieldHandlerInterface $next): InputField|null;
}
