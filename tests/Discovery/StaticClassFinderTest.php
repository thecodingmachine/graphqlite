<?php

namespace TheCodingMachine\GraphQLite\Discovery;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use TheCodingMachine\GraphQLite\Fixtures\TestType;
use TheCodingMachine\GraphQLite\Fixtures\Types\FooExtendType;
use TheCodingMachine\GraphQLite\Fixtures\Types\FooType;

#[CoversClass(StaticClassFinder::class)]
class StaticClassFinderTest extends TestCase
{
    public function testYieldsGivenClasses(): void
    {
        $finder = new StaticClassFinder([
            FooType::class,
            TestType::class,
            FooExtendType::class,
        ]);

        $finderWithPath = $finder->withPathFilter(fn (string $path) => str_contains($path, 'FooExtendType.php'));

        $this->assertFoundClasses([
            FooType::class,
            TestType::class,
            FooExtendType::class,
        ], $finder);

        $this->assertFoundClasses([
            FooExtendType::class,
        ], $finderWithPath);
    }

    private function assertFoundClasses(array $expectedClasses, ClassFinder $classFinder): void
    {
        $result = iterator_to_array($classFinder);

        $this->assertContainsOnlyInstancesOf(\ReflectionClass::class, $result);
        $this->assertEqualsCanonicalizing($expectedClasses, array_keys($result));
    }
}