<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Directives\Validation;

use Attribute;
use TheCodingMachine\GraphQLite\Directives\DirectiveDefinition;
use TheCodingMachine\GraphQLite\Directives\DirectiveLocation;
use TheCodingMachine\GraphQLite\Directives\Exceptions\InvalidDirectiveException;

/**
 * Checks that a directive's `#[Attribute(...)]` PHP targets cover every GraphQL location it declares
 * (e.g. a FIELD_DEFINITION directive must allow both TARGET_METHOD and TARGET_PROPERTY).
 *
 * @internal
 */
final class AttributeTargetValidator
{
    /** @throws InvalidDirectiveException */
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
