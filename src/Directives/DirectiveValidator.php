<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Directives;

use Attribute;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\Type;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;
use TheCodingMachine\GraphQLite\Directives\Exceptions\InvalidDirectiveException;

use function in_array;

/**
 * Validates a discovered directive class and resolves its constructor into a list of
 * {@see ResolvedDirectiveArgument}s. Checks:
 *
 *   1. The `#[Attribute(...)]` PHP target covers every declared GraphQL location.
 *   2. PHP `IS_REPEATABLE` matches `DirectiveDefinition::$repeatable`.
 *   3. Each family interface has a matching location declared, and vice versa.
 *   4. Every constructor parameter maps to a supported input type (scalars only for now).
 *
 * Name uniqueness is checked separately in {@see DirectiveRegistry}, which has the full set to
 * compare against.
 *
 * @internal
 */
final class DirectiveValidator
{
    /**
     * @param class-string<TypeSystemDirective> $directiveClass
     *
     * @return list<ResolvedDirectiveArgument>
     *
     * @throws InvalidDirectiveException
     */
    public static function validate(string $directiveClass, DirectiveDefinition $definition): array
    {
        $reflection = new ReflectionClass($directiveClass);

        $attributeAttributes = $reflection->getAttributes(Attribute::class);
        if ($attributeAttributes === []) {
            throw InvalidDirectiveException::notAttribute($directiveClass);
        }

        $attributeArgs = $attributeAttributes[0]->getArguments();
        $phpFlags = $attributeArgs[0] ?? $attributeArgs['flags'] ?? Attribute::TARGET_ALL;

        self::checkPhpTargets($directiveClass, $definition, $phpFlags);
        self::checkRepeatableParity($directiveClass, $definition, $phpFlags);
        self::checkInterfaceAndLocationAgreement($directiveClass, $definition, $reflection);

        return self::resolveArguments($directiveClass, $reflection);
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

    private static function checkRepeatableParity(string $directiveClass, DirectiveDefinition $definition, int $phpFlags): void
    {
        $phpRepeatable = ($phpFlags & Attribute::IS_REPEATABLE) === Attribute::IS_REPEATABLE;
        if ($phpRepeatable === $definition->repeatable) {
            return;
        }

        throw InvalidDirectiveException::repeatableMismatch($directiveClass, $phpRepeatable, $definition->repeatable);
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

    /**
     * @param ReflectionClass<TypeSystemDirective> $reflection
     *
     * @return list<ResolvedDirectiveArgument>
     */
    private static function resolveArguments(string $directiveClass, ReflectionClass $reflection): array
    {
        $constructor = $reflection->getConstructor();
        if ($constructor === null) {
            return [];
        }

        $arguments = [];
        foreach ($constructor->getParameters() as $parameter) {
            $arguments[] = self::resolveArgument($directiveClass, $parameter);
        }

        return $arguments;
    }

    private static function resolveArgument(string $directiveClass, ReflectionParameter $parameter): ResolvedDirectiveArgument
    {
        $type = $parameter->getType();
        if (! $type instanceof ReflectionNamedType) {
            throw InvalidDirectiveException::unsupportedArgumentType(
                $directiveClass,
                $parameter->getName(),
                'union/intersection types are not supported',
            );
        }

        if ($type->isBuiltin() === false) {
            throw InvalidDirectiveException::unsupportedArgumentType(
                $directiveClass,
                $parameter->getName(),
                'only scalar types (string, int, float, bool) are supported in this release',
            );
        }

        $graphQlType = match ($type->getName()) {
            'string' => Type::string(),
            'int' => Type::int(),
            'float' => Type::float(),
            'bool' => Type::boolean(),
            default => throw InvalidDirectiveException::unsupportedArgumentType(
                $directiveClass,
                $parameter->getName(),
                'PHP type "' . $type->getName() . '" cannot be mapped to a GraphQL scalar',
            ),
        };

        $nullable = $type->allowsNull();
        $finalType = $nullable ? $graphQlType : new NonNull($graphQlType);

        $hasDefault = $parameter->isDefaultValueAvailable();
        $default = $hasDefault ? $parameter->getDefaultValue() : null;

        return new ResolvedDirectiveArgument(
            name: $parameter->getName(),
            type: $finalType,
            hasDefaultValue: $hasDefault,
            defaultValue: $default,
        );
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
