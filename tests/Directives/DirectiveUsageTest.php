<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Tests\Directives;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use TheCodingMachine\GraphQLite\Directives\DirectiveLocation;
use TheCodingMachine\GraphQLite\Directives\DirectiveValidator;
use TheCodingMachine\GraphQLite\Directives\Exceptions\InvalidDirectiveException;
use TheCodingMachine\GraphQLite\Tests\Fixtures\Directives\MisusedOneOfOnType;

final class DirectiveUsageTest extends TestCase
{
    public function testRejectsOneOfAppliedToAnObjectType(): void
    {
        $this->expectException(InvalidDirectiveException::class);
        $this->expectExceptionMessageMatches('/cannot be used on OBJECT/');

        DirectiveValidator::assertDirectivesUsableAt(new ReflectionClass(MisusedOneOfOnType::class), DirectiveLocation::OBJECT);
    }
}
