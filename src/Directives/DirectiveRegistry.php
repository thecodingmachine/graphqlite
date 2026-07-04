<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Directives;

use GraphQL\Type\Definition\Directive as WebonyxDirective;
use ReflectionClass;
use TheCodingMachine\GraphQLite\AnnotationReader;
use TheCodingMachine\GraphQLite\Directives\BuiltIn\Deprecated;
use TheCodingMachine\GraphQLite\Directives\BuiltIn\OneOf;
use TheCodingMachine\GraphQLite\Directives\Exceptions\InvalidDirectiveException;

/**
 * Holds the directives known to a schema. In this layer that's the built-ins that bind PHP behavior
 * to directives webonyx already declares (`@oneOf`, `@deprecated`); a later layer adds user-defined
 * directives on top.
 *
 * The dispatcher middlewares query it at apply time for a directive's argument shape, which the AST
 * builder needs to encode each arg as a GraphQL value.
 */
final class DirectiveRegistry
{
    /** @var array<class-string<DirectiveInterface>, ResolvedDirective> */
    private array $resolvedByClass = [];

    /** Attribute classes that bind PHP behavior to webonyx's built-in directives. */
    private const BUILT_IN_ATTRIBUTES = [
        OneOf::class,
        Deprecated::class,
    ];

    public function __construct(
        private readonly AnnotationReader $annotationReader,
    ) {
    }

    /** Resolve the built-in directives once. Idempotent. */
    public function discover(): void
    {
        foreach (self::BUILT_IN_ATTRIBUTES as $directiveClass) {
            /** @var class-string<TypeSystemDirective> $directiveClass */
            if (isset($this->resolvedByClass[$directiveClass])) {
                continue;
            }

            $this->resolvedByClass[$directiveClass] = DirectiveResolver::resolve($directiveClass, $directiveClass::definition());
        }
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
