<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Directives\Validation;

use ReflectionClass;
use TheCodingMachine\GraphQLite\Directives\DirectiveDefinition;
use TheCodingMachine\GraphQLite\Directives\DirectiveLocation;
use TheCodingMachine\GraphQLite\Directives\Exceptions\InvalidDirectiveException;
use TheCodingMachine\GraphQLite\Directives\FieldDirective;
use TheCodingMachine\GraphQLite\Directives\InputFieldDirective;
use TheCodingMachine\GraphQLite\Directives\InputObjectTypeDirective;
use TheCodingMachine\GraphQLite\Directives\ObjectTypeDirective;
use TheCodingMachine\GraphQLite\Directives\TypeSystemDirective;

use function in_array;

/**
 * Checks a directive's family interfaces and declared locations agree (FieldDirective and
 * FIELD_DEFINITION imply each other, likewise for the input-field, object and input-object families).
 *
 * @internal
 */
final class InterfaceLocationValidator
{
    /** @param ReflectionClass<TypeSystemDirective> $reflection */
    public static function validate(string $directiveClass, DirectiveDefinition $definition, ReflectionClass $reflection): void
    {
        $locations = $definition->locations;
        $interfacePairs = [
            FieldDirective::class => DirectiveLocation::FIELD_DEFINITION,
            InputFieldDirective::class => DirectiveLocation::INPUT_FIELD_DEFINITION,
            ObjectTypeDirective::class => DirectiveLocation::OBJECT,
            InputObjectTypeDirective::class => DirectiveLocation::INPUT_OBJECT,
        ];

        foreach ($interfacePairs as $interface => $expectedLocation) {
            $implements = $reflection->implementsInterface($interface);
            $declaresLocation = in_array($expectedLocation, $locations, true);

            if ($implements && ! $declaresLocation) {
                throw InvalidDirectiveException::interfaceWithoutMatchingLocation($directiveClass, $interface, $locations);
            }

            if (! $implements && $declaresLocation) {
                throw InvalidDirectiveException::locationWithoutMatchingInterface($directiveClass, $expectedLocation, $interface);
            }
        }
    }
}
