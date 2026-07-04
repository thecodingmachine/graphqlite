<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Directives;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use TheCodingMachine\GraphQLite\AnnotationReader;
use TheCodingMachine\GraphQLite\Directives\BuiltIn\Deprecated;
use TheCodingMachine\GraphQLite\Directives\BuiltIn\OneOf;
use TheCodingMachine\GraphQLite\Directives\Exceptions\InvalidDirectiveException;
use TheCodingMachine\GraphQLite\Fixtures\Directives\MisusedOneOfOnType;

final class DirectiveRegistryTest extends TestCase
{
    private function registry(): DirectiveRegistry
    {
        $registry = new DirectiveRegistry(new AnnotationReader());
        $registry->discover();

        return $registry;
    }

    public function testRegistersTheBuiltInDirectives(): void
    {
        $registry = $this->registry();

        $this->assertNotNull($registry->definitionFor(OneOf::class));
        $this->assertNotNull($registry->definitionFor(Deprecated::class));
    }

    public function testBuiltInsAreNotAddedToTheSchemaDirectiveList(): void
    {
        // webonyx already declares @oneOf and @deprecated, so the registry contributes nothing to
        // SchemaConfig::$directives.
        $registry = $this->registry();

        $this->assertSame([], $registry->webonyxDirectives());
        $this->assertFalse($registry->hasAny());
    }

    public function testResolvesBuiltInArguments(): void
    {
        $arguments = $this->registry()->argumentsFor(Deprecated::class);

        $this->assertCount(1, $arguments);
        $this->assertSame('reason', $arguments[0]->name);
    }

    public function testDiscoverIsIdempotent(): void
    {
        $registry = new DirectiveRegistry(new AnnotationReader());
        $registry->discover();
        $registry->discover();

        $this->assertNotNull($registry->definitionFor(OneOf::class));
    }

    public function testObjectTypeDirectivesRejectsMisplacedDirective(): void
    {
        $this->expectException(InvalidDirectiveException::class);
        $this->expectExceptionMessageMatches('/cannot be used on OBJECT/');

        $this->registry()->objectTypeDirectives(new ReflectionClass(MisusedOneOfOnType::class));
    }
}
