<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Directives;

use Attribute;
use ReflectionClass;
use TheCodingMachine\GraphQLite\Directives\Exceptions\InvalidDirectiveException;
use TheCodingMachine\GraphQLite\Directives\Validation\AttributeTargetValidator;
use TheCodingMachine\GraphQLite\Directives\Validation\InterfaceLocationValidator;

use function in_array;
use function is_a;

/**
 * Validates directives at two points:
 *
 *   - {@see self::validate()} at discovery time, delegating to {@see AttributeTargetValidator} (the
 *     `#[Attribute(...)]` PHP targets cover every declared GraphQL location) and
 *     {@see InterfaceLocationValidator} (each family interface has a matching location, and vice
 *     versa).
 *   - {@see self::assertDirectivesUsableAt()} at apply time: a directive placed on a class is
 *     allowed at that class's location. PHP's `#[Attribute]` targets can't tell a `#[Type]` class
 *     from an `#[Input]` class (both are `TARGET_CLASS`), so `#[OneOf]` could be placed on the wrong
 *     kind of class; this reports it instead of letting the collectors drop it silently.
 *
 * Producing the arguments, repeatability, and webonyx directive is {@see DirectiveResolver}'s job;
 * name uniqueness is checked in {@see DirectiveRegistry}.
 *
 * @internal
 */
final class DirectiveValidator
{
    /**
     * @param class-string<TypeSystemDirective> $directiveClass
     *
     * @throws InvalidDirectiveException
     */
    public static function validate(string $directiveClass, DirectiveDefinition $definition): void
    {
        $reflection = new ReflectionClass($directiveClass);

        if ($reflection->getAttributes(Attribute::class) === []) {
            throw InvalidDirectiveException::notAttribute($directiveClass);
        }

        AttributeTargetValidator::validate($directiveClass, $definition, DirectiveReflection::attributeFlags($reflection));
        InterfaceLocationValidator::validate($directiveClass, $definition, $reflection);
    }

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
