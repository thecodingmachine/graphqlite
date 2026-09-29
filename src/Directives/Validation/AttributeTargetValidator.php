<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Directives\Validation;

use Attribute;
use TheCodingMachine\GraphQLite\Directives\DirectiveDefinition;
use TheCodingMachine\GraphQLite\Directives\DirectiveLocation;
use TheCodingMachine\GraphQLite\Directives\Exceptions\InvalidDirectiveException;

/**
 * Checks a directive's `#[Attribute(...)]` targets cover every GraphQL location it declares.
 *
 * @internal
 */
final class AttributeTargetValidator
{
    public static function validate(string $directiveClass, DirectiveDefinition $definition, int $phpFlags): void
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

    /** @return array<int, string> required TARGET_* flag => label */
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
            // No apply hooks for the other locations yet.
            default => [],
        };
    }
}
