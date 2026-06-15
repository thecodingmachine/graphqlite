<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Directives;

use PHPUnit\Framework\TestCase;
use TheCodingMachine\GraphQLite\Directives\Exceptions\InvalidDirectiveException;
use TheCodingMachine\GraphQLite\Fixtures\Directives\Invalid\InterfaceWithoutLocationDirective;
use TheCodingMachine\GraphQLite\Fixtures\Directives\Invalid\MissingPhpTargetDirective;

final class DirectiveValidatorTest extends TestCase
{
    public function testRejectsDirectiveMissingRequiredPhpTarget(): void
    {
        $this->expectException(InvalidDirectiveException::class);
        $this->expectExceptionMessageMatches('/TARGET_METHOD/');

        DirectiveValidator::validate(MissingPhpTargetDirective::class, MissingPhpTargetDirective::definition());
    }

    public function testRejectsInterfaceWithoutMatchingLocation(): void
    {
        $this->expectException(InvalidDirectiveException::class);
        $this->expectExceptionMessageMatches('/FieldDirective/');

        DirectiveValidator::validate(InterfaceWithoutLocationDirective::class, InterfaceWithoutLocationDirective::definition());
    }
}
