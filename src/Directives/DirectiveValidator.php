<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Directives;

use Attribute;
use ReflectionClass;
use TheCodingMachine\GraphQLite\Directives\Exceptions\InvalidDirectiveException;

use function in_array;
use function is_a;

/**
 * Validates directives at two points:
 *
 *   - {@see self::validate()} at discovery time: the `#[Attribute(...)]` PHP target covers every
 *     declared GraphQL location, and each family interface has a matching location declared (and
 *     vice versa).
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

        self::checkPhpTargets($directiveClass, $definition, DirectiveReflection::attributeFlags($reflection));
        self::checkInterfaceAndLocationAgreement($directiveClass, $definition, $reflection);
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

    private static function checkPhpTargets(string $directiveClass, DirectiveDefinition $definition, int $phpFlags): void
    {
        foreach ($definition->locations as $location) {
            foreach (self::requiredPhpTargetsFor($location) as $requiredTarget => $label) {
                if (($phpFlags & $requiredTarget) === $requiredTarget) {
                    continue;
                }

                throw InvalidDirectiveException::phpTargetMissingForLocation($directiveClass, $location, $label);
            }
        }
    }

    /** @param ReflectionClass<TypeSystemDirective> $reflection */
    private static function checkInterfaceAndLocationAgreement(string $directiveClass, DirectiveDefinition $definition, ReflectionClass $reflection): void
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

    /** @return array<int, string> map of Attribute::TARGET_* flag → human-readable label */
    private static function requiredPhpTargetsFor(DirectiveLocation $location): array
    {
        return match ($location) {
            DirectiveLocation::FIELD_DEFINITION => [
                Attribute::TARGET_METHOD => 'TARGET_METHOD',
                Attribute::TARGET_PROPERTY => 'TARGET_PROPERTY',
            ],
            DirectiveLocation::INPUT_FIELD_DEFINITION => [
                Attribute::TARGET_METHOD => 'TARGET_METHOD',
                Attribute::TARGET_PROPERTY => 'TARGET_PROPERTY',
                Attribute::TARGET_PARAMETER => 'TARGET_PARAMETER',
            ],
            DirectiveLocation::OBJECT => [Attribute::TARGET_CLASS => 'TARGET_CLASS'],
            DirectiveLocation::INPUT_OBJECT => [Attribute::TARGET_CLASS => 'TARGET_CLASS'],
            // Other locations don't have apply hooks yet, so there's nothing to enforce.
            default => [],
        };
    }
}
