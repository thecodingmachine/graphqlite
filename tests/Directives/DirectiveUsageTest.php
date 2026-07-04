<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Directives;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use TheCodingMachine\GraphQLite\Directives\Exceptions\InvalidDirectiveException;
use TheCodingMachine\GraphQLite\Fixtures\Directives\MisusedOneOfOnType;

final class DirectiveUsageTest extends TestCase
{
    public function testRejectsOneOfAppliedToAnObjectType(): void
    {
        $this->expectException(InvalidDirectiveException::class);
        $this->expectExceptionMessageMatches('/cannot be used on OBJECT/');

        DirectiveValidator::assertDirectivesUsableAt(new ReflectionClass(MisusedOneOfOnType::class), DirectiveLocation::OBJECT);
    }
}
