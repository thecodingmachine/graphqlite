<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Directives;

use Attribute;
use GraphQL\Type\Definition\Directive as WebonyxDirective;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\Type;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;
use TheCodingMachine\GraphQLite\Directives\Exceptions\InvalidDirectiveException;

use function array_map;

/**
 * Turns a validated directive class into the pieces the registry caches: its constructor arguments,
 * and (for non-built-in directives) the webonyx {@see WebonyxDirective} to register on the schema.
 *
 * Repeatability comes straight from the class's `#[Attribute]` flags, not {@see DirectiveDefinition}.
 *
 * @internal
 */
final class DirectiveResolver
{
    /**
     * @param class-string<TypeSystemDirective> $directiveClass
     *
     * @throws InvalidDirectiveException when a constructor argument can't map to a GraphQL input type.
     */
    public static function resolve(string $directiveClass, DirectiveDefinition $definition): ResolvedDirective
    {
        $reflection = new ReflectionClass($directiveClass);
        $arguments = self::resolveArguments($directiveClass, $reflection);

        // Built-in directives are declared by webonyx, so there's no directive to register for them.
        $webonyxDirective = $definition->builtIn
            ? null
            : self::buildWebonyxDirective($definition, $arguments, self::isRepeatable($reflection));

        return new ResolvedDirective($definition, $arguments, $webonyxDirective);
    }

    /**
     * @param class-string<TypeSystemDirective> $directiveClass
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

    /** @param ReflectionClass<TypeSystemDirective> $reflection */
    private static function isRepeatable(ReflectionClass $reflection): bool
    {
        return (DirectiveReflection::attributeFlags($reflection) & Attribute::IS_REPEATABLE) === Attribute::IS_REPEATABLE;
    }

    /** @param list<ResolvedDirectiveArgument> $arguments */
    private static function buildWebonyxDirective(DirectiveDefinition $definition, array $arguments, bool $repeatable): WebonyxDirective
    {
        $argsConfig = [];
        foreach ($arguments as $argument) {
            $config = ['type' => $argument->type];
            if ($argument->hasDefaultValue) {
                $config['defaultValue'] = $argument->defaultValue;
            }
            if ($argument->description !== null) {
                $config['description'] = $argument->description;
            }
            $argsConfig[$argument->name] = $config;
        }

        $config = [
            'name' => $definition->name,
            'locations' => array_map(static fn (DirectiveLocation $loc) => $loc->value, $definition->locations),
            'isRepeatable' => $repeatable,
            'args' => $argsConfig,
        ];
        if ($definition->description !== null) {
            $config['description'] = $definition->description;
        }

        return new WebonyxDirective($config);
    }
}
