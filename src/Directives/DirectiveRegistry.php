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
use function in_array;

/**
 * Discovers, validates and holds a schema's directives: the user-defined ones plus the built-ins
 * (`@oneOf`, `@deprecated`). Middlewares query it at apply time for a directive's argument shape.
 */
final class DirectiveRegistry
{
    /** @var array<class-string<DirectiveInterface>, ResolvedDirective> */
    private array $resolvedByClass = [];

    /** @var array<string, class-string<DirectiveInterface>> */
    private array $classByName = [];

    /** Attributes we bind to webonyx's built-ins; a custom directive reuses these names only via `builtIn: true`. */
    private const BUILT_IN_ATTRIBUTES = [
        OneOf::class,
        Deprecated::class,
    ];

    /** webonyx directives we don't bind; no custom directive may take these names. */
    private const RESERVED_WEBONYX_NAMES = [
        WebonyxDirective::SKIP_NAME,
        WebonyxDirective::INCLUDE_NAME,
        WebonyxDirective::SPECIFIED_BY_NAME,
    ];

    public function __construct(
        private readonly AnnotationReader $annotationReader,
        private readonly DirectiveClassFinder $classFinder,
    ) {
    }

    /** Idempotent. User directives first, so a `builtIn: true` override lands before the bundled copy. */
    public function discover(): void
    {
        foreach ($this->classFinder->findDirectives() as $directiveClass) {
            $this->register($directiveClass);
        }
        foreach (self::BUILT_IN_ATTRIBUTES as $directiveClass) {
            $this->register($directiveClass);
        }
    }

    /** @param class-string<TypeSystemDirective> $directiveClass */
    private function register(string $directiveClass): void
    {
        // Discovery and the built-in list overlap, so a repeat is expected.
        if (isset($this->resolvedByClass[$directiveClass])) {
            return;
        }

        $definition = $directiveClass::definition();

        DirectiveValidator::validate($directiveClass, $definition);

        if (! $definition->builtIn && in_array($definition->name, self::RESERVED_WEBONYX_NAMES, true)) {
            throw InvalidDirectiveException::reservedName($definition->name, $directiveClass);
        }

        if (! $definition->builtIn && $this->isReservedBuiltInName($definition->name)) {
            throw InvalidDirectiveException::reservedName($definition->name, $directiveClass);
        }

        if (array_key_exists($definition->name, $this->classByName)) {
            // A builtIn override defers to the already-registered user class; two customs is an error.
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
