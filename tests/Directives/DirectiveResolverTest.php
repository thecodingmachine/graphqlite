<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Directives;

use GraphQL\Type\Definition\Directive as WebonyxDirective;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\StringType;
use PHPUnit\Framework\TestCase;
use TheCodingMachine\GraphQLite\Directives\Exceptions\InvalidDirectiveException;
use TheCodingMachine\GraphQLite\Fixtures\Directives\AuditFieldDirective;
use TheCodingMachine\GraphQLite\Fixtures\Directives\Invalid\UnsupportedArgumentTypeDirective;
use TheCodingMachine\GraphQLite\Fixtures\Directives\UppercaseFieldDirective;

final class DirectiveResolverTest extends TestCase
{
    public function testResolvesNoArgsToEmptyList(): void
    {
        $resolved = DirectiveResolver::resolve(UppercaseFieldDirective::class, UppercaseFieldDirective::definition());

        $this->assertSame([], $resolved->arguments);
    }

    public function testResolvesScalarArgumentWithCorrectTypeAndNullability(): void
    {
        $resolved = DirectiveResolver::resolve(AuditFieldDirective::class, AuditFieldDirective::definition());

        $this->assertCount(1, $resolved->arguments);
        $this->assertSame('reason', $resolved->arguments[0]->name);
        $this->assertInstanceOf(NonNull::class, $resolved->arguments[0]->type);
        $this->assertInstanceOf(StringType::class, $resolved->arguments[0]->type->getWrappedType());
        $this->assertFalse($resolved->arguments[0]->hasDefaultValue);
    }

    public function testInfersRepeatableFromAttributeFlags(): void
    {
        $audit = DirectiveResolver::resolve(AuditFieldDirective::class, AuditFieldDirective::definition())->webonyxDirective;
        $uppercase = DirectiveResolver::resolve(UppercaseFieldDirective::class, UppercaseFieldDirective::definition())->webonyxDirective;

        $this->assertInstanceOf(WebonyxDirective::class, $audit);
        $this->assertTrue($audit->isRepeatable);

        $this->assertInstanceOf(WebonyxDirective::class, $uppercase);
        $this->assertFalse($uppercase->isRepeatable);
    }

    public function testRejectsUnsupportedArgumentType(): void
    {
        $this->expectException(InvalidDirectiveException::class);
        $this->expectExceptionMessageMatches('/scalar types/');

        DirectiveResolver::resolve(UnsupportedArgumentTypeDirective::class, UnsupportedArgumentTypeDirective::definition());
    }
}
