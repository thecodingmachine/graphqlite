<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Directives;

use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\StringType;
use PHPUnit\Framework\TestCase;
use TheCodingMachine\GraphQLite\Directives\Exceptions\InvalidDirectiveException;
use TheCodingMachine\GraphQLite\Fixtures\Directives\AuditFieldDirective;
use TheCodingMachine\GraphQLite\Fixtures\Directives\Invalid\InterfaceWithoutLocationDirective;
use TheCodingMachine\GraphQLite\Fixtures\Directives\Invalid\MissingPhpTargetDirective;
use TheCodingMachine\GraphQLite\Fixtures\Directives\Invalid\RepeatableMismatchDirective;
use TheCodingMachine\GraphQLite\Fixtures\Directives\Invalid\UnsupportedArgumentTypeDirective;
use TheCodingMachine\GraphQLite\Fixtures\Directives\UppercaseFieldDirective;

final class DirectiveValidatorTest extends TestCase
{
    public function testValidDirectiveWithNoArgsResolvesToEmptyArgumentList(): void
    {
        $args = DirectiveValidator::validate(UppercaseFieldDirective::class, UppercaseFieldDirective::definition());

        $this->assertSame([], $args);
    }

    public function testResolvesScalarArgumentWithCorrectTypeAndNullability(): void
    {
        $args = DirectiveValidator::validate(AuditFieldDirective::class, AuditFieldDirective::definition());

        $this->assertCount(1, $args);
        $this->assertSame('reason', $args[0]->name);
        $this->assertInstanceOf(NonNull::class, $args[0]->type);
        $this->assertInstanceOf(StringType::class, $args[0]->type->getWrappedType());
        $this->assertFalse($args[0]->hasDefaultValue);
    }

    public function testRejectsDirectiveMissingRequiredPhpTarget(): void
    {
        $this->expectException(InvalidDirectiveException::class);
        $this->expectExceptionMessageMatches('/TARGET_METHOD/');

        DirectiveValidator::validate(MissingPhpTargetDirective::class, MissingPhpTargetDirective::definition());
    }

    public function testRejectsRepeatableMismatch(): void
    {
        $this->expectException(InvalidDirectiveException::class);
        $this->expectExceptionMessageMatches('/repeatable/');

        DirectiveValidator::validate(RepeatableMismatchDirective::class, RepeatableMismatchDirective::definition());
    }

    public function testRejectsInterfaceWithoutMatchingLocation(): void
    {
        $this->expectException(InvalidDirectiveException::class);
        $this->expectExceptionMessageMatches('/FieldDirective/');

        DirectiveValidator::validate(InterfaceWithoutLocationDirective::class, InterfaceWithoutLocationDirective::definition());
    }

    public function testRejectsUnsupportedArgumentType(): void
    {
        $this->expectException(InvalidDirectiveException::class);
        $this->expectExceptionMessageMatches('/scalar types/');

        DirectiveValidator::validate(UnsupportedArgumentTypeDirective::class, UnsupportedArgumentTypeDirective::definition());
    }
}
