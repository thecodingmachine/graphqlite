<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Directives;

use ReflectionClass;
use TheCodingMachine\GraphQLite\Directives\Exceptions\InvalidDirectiveException;

use function in_array;
use function is_a;

/**
 * Validates that directives are applied where they're allowed. PHP's `#[Attribute]` targets can't
 * tell a `#[Type]` class from an `#[Input]` class (both are `TARGET_CLASS`), so a class-level
 * directive like `#[OneOf]` could be placed on the wrong kind of class. This reports that instead
 * of letting the interface-based collectors drop the misplaced directive silently.
 *
 * @internal
 */
final class DirectiveValidator
{
    /**
     * @param ReflectionClass<object> $refClass
     *
     * @throws InvalidDirectiveException when a directive on $refClass isn't allowed at $location.
     */
    public static function assertDirectivesUsableAt(ReflectionClass $refClass, DirectiveLocation $location): void
    {
        foreach ($refClass->getAttributes() as $attribute) {
            $directiveClass = $attribute->getName();
            if (! is_a($directiveClass, TypeSystemDirective::class, true)) {
                continue;
            }

            $locations = $directiveClass::definition()->locations;
            if (in_array($location, $locations, true)) {
                continue;
            }

            throw InvalidDirectiveException::notUsableAtLocation($directiveClass, $location, $locations);
        }
    }
}
