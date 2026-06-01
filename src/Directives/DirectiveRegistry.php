<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Directives;

use GraphQL\Type\Definition\Directive as WebonyxDirective;
use TheCodingMachine\GraphQLite\Directives\BuiltIn\OneOf;
use TheCodingMachine\GraphQLite\Directives\Discovery\DirectiveClassFinder;
use TheCodingMachine\GraphQLite\Directives\Exceptions\InvalidDirectiveException;

use function array_key_exists;
use function assert;
use function in_array;
use function method_exists;

/**
 * Holds the custom directives for a schema, built once in
 * {@see \TheCodingMachine\GraphQLite\SchemaFactory::createSchema()}. For each discovered class it
 * runs {@see DirectiveValidator}, caches what {@see DirectiveResolver} produces, and enforces name
 * uniqueness across the set.
 *
 * The dispatcher middlewares query it at apply time for a directive's argument shape, which the AST
 * builder needs to encode each arg as a GraphQL value.
 */
final class DirectiveRegistry
{
    /** @var array<class-string<DirectiveInterface>, ResolvedDirective> */
    private array $resolvedByClass = [];

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
        // User classes first: an override of a built-in (same name, builtIn: true) needs to land
        // before our bundled copy registers.
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
        // Registering the same class twice is a no-op; discovery and the built-in list share this
        // registry, so duplicates are expected.
        if (isset($this->resolvedByClass[$directiveClass])) {
            return;
        }

        if (! method_exists($directiveClass, 'definition')) {
            throw InvalidDirectiveException::noDefinitionMethod($directiveClass);
        }

        $definition = $directiveClass::definition();
        assert($definition instanceof DirectiveDefinition);

        DirectiveValidator::validate($directiveClass, $definition);

        if (! $definition->builtIn && in_array($definition->name, self::RESERVED_NAMES, true)) {
            throw InvalidDirectiveException::reservedName($definition->name, $directiveClass);
        }

        if (array_key_exists($definition->name, $this->classByName)) {
            // A name clash is fine when this side is also built-in: the user supplied their own
            // implementation and we defer to it. Two custom directives sharing a name is an error.
            if ($definition->builtIn) {
                return;
            }
            throw InvalidDirectiveException::duplicateName(
                $definition->name,
                $this->classByName[$definition->name],
                $directiveClass,
            );
        }

        $this->resolvedByClass[$directiveClass] = DirectiveResolver::resolve($directiveClass, $definition);
        $this->classByName[$definition->name] = $directiveClass;
    }

    public function hasAny(): bool
    {
        foreach ($this->resolvedByClass as $resolved) {
            if ($resolved->webonyxDirective !== null) {
                return true;
            }
        }

        return false;
    }

    /** @return list<WebonyxDirective> */
    public function webonyxDirectives(): array
    {
        $directives = [];
        foreach ($this->resolvedByClass as $resolved) {
            if ($resolved->webonyxDirective === null) {
                continue;
            }
            $directives[] = $resolved->webonyxDirective;
        }

        return $directives;
    }

    /**
     * The argument shape for a directive class.
     *
     * @param class-string<DirectiveInterface> $directiveClass
     *
     * @return list<ResolvedDirectiveArgument>
     */
    public function argumentsFor(string $directiveClass): array
    {
        $resolved = $this->resolvedByClass[$directiveClass] ?? null;

        return $resolved === null ? [] : $resolved->arguments;
    }

    /**
     * The metadata for a directive class, or null if it isn't registered.
     *
     * @param class-string<DirectiveInterface> $directiveClass
     */
    public function definitionFor(string $directiveClass): DirectiveDefinition|null
    {
        return ($this->resolvedByClass[$directiveClass] ?? null)?->definition;
    }
}
