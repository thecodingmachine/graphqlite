<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Directives;

use GraphQL\Type\Definition\Directive as WebonyxDirective;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\StringType;
use PHPUnit\Framework\TestCase;
use TheCodingMachine\GraphQLite\Directives\Exceptions\InvalidDirectiveException;
use TheCodingMachine\GraphQLite\Fixtures\Directives\InternalFieldDirective;
use TheCodingMachine\GraphQLite\Fixtures\Directives\Invalid\UnsupportedArgumentTypeDirective;
use TheCodingMachine\GraphQLite\Fixtures\Directives\NoteFieldDirective;

final class DirectiveResolverTest extends TestCase
{
    public function testResolvesNoArgsToEmptyList(): void
    {
        $resolved = DirectiveResolver::resolve(InternalFieldDirective::class, InternalFieldDirective::definition());

        $this->assertSame([], $resolved->arguments);
    }

    public function testResolvesScalarArgumentWithCorrectTypeAndNullability(): void
    {
        $resolved = DirectiveResolver::resolve(NoteFieldDirective::class, NoteFieldDirective::definition());

        $this->assertCount(1, $resolved->arguments);
        $this->assertSame('text', $resolved->arguments[0]->name);
        $this->assertInstanceOf(NonNull::class, $resolved->arguments[0]->type);
        $this->assertInstanceOf(StringType::class, $resolved->arguments[0]->type->getWrappedType());
        $this->assertFalse($resolved->arguments[0]->hasDefaultValue);
    }

    public function testInfersRepeatableFromAttributeFlags(): void
    {
        $note = DirectiveResolver::resolve(NoteFieldDirective::class, NoteFieldDirective::definition())->webonyxDirective;
        $internal = DirectiveResolver::resolve(InternalFieldDirective::class, InternalFieldDirective::definition())->webonyxDirective;

        $this->assertInstanceOf(WebonyxDirective::class, $note);
        $this->assertTrue($note->isRepeatable);

        $this->assertInstanceOf(WebonyxDirective::class, $internal);
        $this->assertFalse($internal->isRepeatable);
    }

    public function testRejectsUnsupportedArgumentType(): void
    {
        $this->expectException(InvalidDirectiveException::class);
        $this->expectExceptionMessageMatches('/scalar types/');

        DirectiveResolver::resolve(UnsupportedArgumentTypeDirective::class, UnsupportedArgumentTypeDirective::definition());
    }
}
