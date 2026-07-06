<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Directives;

use GraphQL\Type\Definition\Directive as WebonyxDirective;
use ReflectionClass;
use TheCodingMachine\GraphQLite\AnnotationReader;
use TheCodingMachine\GraphQLite\Directives\BuiltIn\Deprecated;
use TheCodingMachine\GraphQLite\Directives\BuiltIn\OneOf;
use TheCodingMachine\GraphQLite\Directives\Discovery\DirectiveClassFinder;
use TheCodingMachine\GraphQLite\Directives\Exceptions\InvalidDirectiveException;

use function array_key_exists;
use function assert;
use function method_exists;

/**
 * Holds the directives known to a schema: the user-defined directives discovered in the configured
 * namespaces plus the built-ins that bind PHP behavior to directives webonyx already declares
 * (`@oneOf`, `@deprecated`). For each discovered class it runs {@see DirectiveValidator}, caches what
 * {@see DirectiveResolver} produces, and enforces name uniqueness across the set.
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
     * Attribute classes that bind PHP behavior to webonyx's pre-existing built-in directives.
     * Also the source of truth for reserved names: a custom (non-built-in) directive can't claim
     * one of these names unless it declares `builtIn: true` to override the bundled binding.
     * Registered after user discovery so such an override wins.
     */
    private const BUILT_IN_ATTRIBUTES = [
        OneOf::class,
        Deprecated::class,
    ];

    public function __construct(
        private readonly AnnotationReader $annotationReader,
        private readonly DirectiveClassFinder $classFinder,
    ) {
    }

    /** Discover + validate the user directives, then the built-ins. Idempotent. */
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

        if (! $definition->builtIn && $this->isReservedBuiltInName($definition->name)) {
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

    /** Whether $name is bound by one of our built-in attributes, so only a `builtIn: true` override may take it. */
    private function isReservedBuiltInName(string $name): bool
    {
        foreach (self::BUILT_IN_ATTRIBUTES as $builtInClass) {
            if ($builtInClass::definition()->name === $name) {
                return true;
            }
        }

        return false;
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

    /**
     * The object-type directives applied to a class, after checking each is allowed at the OBJECT
     * location — e.g. rejecting `#[OneOf]` (an INPUT_OBJECT directive) on a `#[Type]`.
     *
     * @param ReflectionClass<object> $refClass
     *
     * @return list<ObjectTypeDirective>
     *
     * @throws InvalidDirectiveException
     */
    public function objectTypeDirectives(ReflectionClass $refClass): array
    {
        DirectiveValidator::assertDirectivesUsableAt($refClass, DirectiveLocation::OBJECT);

        return $this->annotationReader->getObjectTypeDirectives($refClass);
    }

    /**
     * The input-object directives applied to a class, after checking each is allowed at the
     * INPUT_OBJECT location.
     *
     * @param ReflectionClass<object> $refClass
     *
     * @return list<InputObjectTypeDirective>
     *
     * @throws InvalidDirectiveException
     */
    public function inputObjectTypeDirectives(ReflectionClass $refClass): array
    {
        DirectiveValidator::assertDirectivesUsableAt($refClass, DirectiveLocation::INPUT_OBJECT);

        return $this->annotationReader->getInputObjectTypeDirectives($refClass);
    }
}
