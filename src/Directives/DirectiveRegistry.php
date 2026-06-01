<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Directives;

use GraphQL\Type\Definition\Directive as WebonyxDirective;
use TheCodingMachine\GraphQLite\Directives\BuiltIn\OneOf;
use TheCodingMachine\GraphQLite\Directives\Discovery\DirectiveClassFinder;
use TheCodingMachine\GraphQLite\Directives\Exceptions\InvalidDirectiveException;

use function array_key_exists;
use function array_map;
use function array_values;
use function assert;
use function in_array;
use function method_exists;

/**
 * The single source of truth for custom directives in a schema. Built once during
 * {@see \TheCodingMachine\GraphQLite\SchemaFactory::createSchema()} by discovering directive
 * classes via {@see DirectiveClassFinder}, validating them with {@see DirectiveValidator}, and
 * caching the resolved {@see DirectiveDefinition}, the constructor-argument shape, and the
 * webonyx-side {@see WebonyxDirective} per class.
 *
 * The registry is also consulted at apply time by the dispatcher middlewares to look up an
 * argument shape from a directive instance's class (so the AST node builder knows how to encode
 * each arg as a GraphQL value).
 */
final class DirectiveRegistry
{
    /** @var array<class-string<DirectiveInterface>, DirectiveDefinition> */
    private array $definitionsByClass = [];

    /** @var array<class-string<DirectiveInterface>, list<ResolvedDirectiveArgument>> */
    private array $argumentsByClass = [];

    /** @var array<class-string<DirectiveInterface>, WebonyxDirective> */
    private array $webonyxByClass = [];

    /** @var array<string, class-string<DirectiveInterface>> */
    private array $classByName = [];

    /**
     * Names of webonyx-built-in directives. Custom (non-built-in) directives cannot reuse them.
     */
    private const RESERVED_NAMES = ['skip', 'include', 'deprecated', 'oneOf'];

    /**
     * Attribute classes that bind PHP behavior to webonyx's pre-existing built-in directives.
     * Registered after user discovery so a user-provided override (a class with the same name and
     * `builtIn: true`) wins.
     */
    private const BUILT_IN_ATTRIBUTES = [
        OneOf::class,
    ];

    public function __construct(
        private readonly DirectiveClassFinder $classFinder,
    ) {
    }

    /** Run discovery + validation once. Idempotent. */
    public function discover(): void
    {
        // User classes first so a user-supplied override of a built-in (a class with `builtIn:
        // true` and the same name) lands before our bundled copy gets a chance to register.
        foreach ($this->classFinder->findDirectives() as $directiveClass) {
            $this->register($directiveClass);
        }
        foreach (self::BUILT_IN_ATTRIBUTES as $directiveClass) {
            $this->register($directiveClass);
        }
    }

    /**
     * @param class-string<TypeSystemDirective> $directiveClass
     *
     * @throws InvalidDirectiveException
     */
    private function register(string $directiveClass): void
    {
        // Same FQCN registered twice is a no-op — discovery, the built-in list, and user code all
        // share the registry, so collisions on the same class are expected.
        if (isset($this->definitionsByClass[$directiveClass])) {
            return;
        }

        if (! method_exists($directiveClass, 'definition')) {
            throw InvalidDirectiveException::noDefinitionMethod($directiveClass);
        }

        $definition = $directiveClass::definition();
        assert($definition instanceof DirectiveDefinition);

        $arguments = DirectiveValidator::validate($directiveClass, $definition);

        if (! $definition->builtIn && in_array($definition->name, self::RESERVED_NAMES, true)) {
            throw InvalidDirectiveException::reservedName($definition->name, $directiveClass);
        }

        if (array_key_exists($definition->name, $this->classByName)) {
            // A name clash with a built-in is allowed when this side is also a built-in: it means
            // the user has supplied their own implementation and we defer. Two non-built-ins with
            // the same name remain an error.
            if ($definition->builtIn) {
                return;
            }
            throw InvalidDirectiveException::duplicateName(
                $definition->name,
                $this->classByName[$definition->name],
                $directiveClass,
            );
        }

        $this->definitionsByClass[$directiveClass] = $definition;
        $this->argumentsByClass[$directiveClass] = $arguments;
        $this->classByName[$definition->name] = $directiveClass;

        // Built-in directives are declared by webonyx itself — don't contribute a duplicate
        // definition to SchemaConfig::$directives.
        if ($definition->builtIn) {
            return;
        }

        $this->webonyxByClass[$directiveClass] = self::buildWebonyxDirective($definition, $arguments);
    }

    /** @param list<ResolvedDirectiveArgument> $arguments */
    private static function buildWebonyxDirective(DirectiveDefinition $definition, array $arguments): WebonyxDirective
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
            'isRepeatable' => $definition->repeatable,
            'args' => $argsConfig,
        ];
        if ($definition->description !== null) {
            $config['description'] = $definition->description;
        }

        return new WebonyxDirective($config);
    }

    public function hasAny(): bool
    {
        return $this->webonyxByClass !== [];
    }

    /** @return list<WebonyxDirective> */
    public function webonyxDirectives(): array
    {
        return array_values($this->webonyxByClass);
    }

    /**
     * Look up the argument shape for a directive class. Accepts any {@see DirectiveInterface} class
     * so it composes with future executable-directive lookups.
     *
     * @param class-string<DirectiveInterface> $directiveClass
     *
     * @return list<ResolvedDirectiveArgument>
     */
    public function argumentsFor(string $directiveClass): array
    {
        return $this->argumentsByClass[$directiveClass] ?? [];
    }

    /**
     * Look up the declarative metadata for a directive class. Accepts any
     * {@see DirectiveInterface} class for the same reason as {@see argumentsFor}.
     *
     * @param class-string<DirectiveInterface> $directiveClass
     */
    public function definitionFor(string $directiveClass): DirectiveDefinition|null
    {
        return $this->definitionsByClass[$directiveClass] ?? null;
    }
}
