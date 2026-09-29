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
 * Validates a directive at discovery time ({@see self::validate()}, delegating target and
 * interface/location checks) and its placement at apply time ({@see self::assertDirectivesUsableAt()}).
 *
 * @internal
 */
final class DirectiveValidator
{
    /** @param class-string<TypeSystemDirective> $directiveClass */
    public static function validate(string $directiveClass, DirectiveDefinition $definition): void
    {
        $reflection = new ReflectionClass($directiveClass);

        if ($reflection->getAttributes(Attribute::class) === []) {
            throw InvalidDirectiveException::notAttribute($directiveClass);
        }

        AttributeTargetValidator::validate($directiveClass, $definition, DirectiveReflection::attributeFlags($reflection));
        InterfaceLocationValidator::validate($directiveClass, $definition, $reflection);
    }

    /** @param ReflectionClass<object> $refClass */
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
