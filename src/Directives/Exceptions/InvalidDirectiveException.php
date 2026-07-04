<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Directives\Exceptions;

use TheCodingMachine\GraphQLite\Directives\DirectiveLocation;
use TheCodingMachine\GraphQLite\GraphQLRuntimeException;

use function array_map;
use function implode;
use function sprintf;

/**
 * Thrown at schema build time when a directive declaration is invalid, or when a directive is
 * applied somewhere it isn't allowed — e.g. a bad PHP target, an unsupported argument type, a name
 * clash, or `#[OneOf]` on a `#[Type]`.
 */
final class InvalidDirectiveException extends GraphQLRuntimeException
{
    /**
     * A directive was applied at a location it doesn't declare — e.g. `#[OneOf]` on a `#[Type]`.
     *
     * @param list<DirectiveLocation> $declaredLocations
     */
    public static function notUsableAtLocation(string $directiveClass, DirectiveLocation $attempted, array $declaredLocations): self
    {
        $declared = implode(', ', array_map(static fn (DirectiveLocation $location) => $location->value, $declaredLocations));

        return new self(sprintf(
            'Directive "%s" cannot be used on %s; it is declared for %s.',
            $directiveClass,
            $attempted->value,
            $declared === '' ? 'no locations' : $declared,
        ));
    }

    public static function phpTargetMissingForLocation(string $directiveClass, DirectiveLocation $location, string $requiredTarget): self
    {
        return new self(sprintf(
            'Directive "%s" declares location %s but its PHP #[Attribute(...)] target does not include %s. ' .
            'Add the missing target to make the directive usable.',
            $directiveClass,
            $location->value,
            $requiredTarget,
        ));
    }

    /** @param list<DirectiveLocation> $locations */
    public static function interfaceWithoutMatchingLocation(string $directiveClass, string $interface, array $locations): self
    {
        $locationsList = implode(', ', array_map(static fn (DirectiveLocation $l) => $l->value, $locations));

        return new self(sprintf(
            'Directive "%s" implements %s but its declared locations [%s] do not include the corresponding GraphQL location. ' .
            'Either remove the interface or add the location to DirectiveDefinition::$locations.',
            $directiveClass,
            $interface,
            $locationsList,
        ));
    }

    public static function locationWithoutMatchingInterface(string $directiveClass, DirectiveLocation $location, string $expectedInterface): self
    {
        return new self(sprintf(
            'Directive "%s" declares location %s but does not implement %s. ' .
            'Either remove the location or implement the corresponding interface.',
            $directiveClass,
            $location->value,
            $expectedInterface,
        ));
    }

    public static function notAttribute(string $directiveClass): self
    {
        return new self(sprintf(
            'Directive "%s" must declare a #[Attribute(...)] declaration so it can be applied to PHP code.',
            $directiveClass,
        ));
    }

    public static function unsupportedArgumentType(string $directiveClass, string $parameterName, string $reason): self
    {
        return new self(sprintf(
            'Directive "%s" constructor parameter "$%s" cannot be mapped to a GraphQL input type: %s. ' .
            'Supported types are scalars (string, int, float, bool), backed enums, and arrays/lists of those.',
            $directiveClass,
            $parameterName,
            $reason,
        ));
    }

    public static function duplicateName(string $name, string $existingClass, string $newClass): self
    {
        return new self(sprintf(
            'Two directives declare the GraphQL name "@%s": %s and %s. Directive names must be unique.',
            $name,
            $existingClass,
            $newClass,
        ));
    }

    public static function reservedName(string $name, string $directiveClass): self
    {
        return new self(sprintf(
            'Directive "%s" declares the name "@%s" which is reserved for a webonyx built-in directive (@skip, @include, @deprecated). Pick a different name.',
            $directiveClass,
            $name,
        ));
    }

    public static function noDefinitionMethod(string $directiveClass): self
    {
        return new self(sprintf(
            'Directive "%s" implements DirectiveInterface but does not declare a static `definition(): DirectiveDefinition` method.',
            $directiveClass,
        ));
    }
}
