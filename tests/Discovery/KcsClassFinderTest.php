<?php

namespace TheCodingMachine\GraphQLite\Discovery;

use Kcs\ClassFinder\Finder\ComposerFinder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use TheCodingMachine\GraphQLite\Fixtures\TestType;
use TheCodingMachine\GraphQLite\Fixtures\Types\AbstractFooType;
use TheCodingMachine\GraphQLite\Fixtures\Types\EnumType;
use TheCodingMachine\GraphQLite\Fixtures\Types\FooExtendType;
use TheCodingMachine\GraphQLite\Fixtures\Types\FooType;
use TheCodingMachine\GraphQLite\Fixtures\Types\GetterSetterType;
use TheCodingMachine\GraphQLite\Fixtures\Types\MagicGetterSetterType;
use TheCodingMachine\GraphQLite\Fixtures\Types\NoTypeAnnotation;
use TheCodingMachine\GraphQLite\Fixtures\Types\TestFactory;

#[CoversClass(KcsClassFinder::class)]
class KcsClassFinderTest extends TestCase
{
    public function testYieldsGivenClasses(): void
    {
        $finder = new KcsClassFinder(
            (new ComposerFinder())
                ->inNamespace('TheCodingMachine\GraphQLite\Fixtures\Types')
        );

        $finderWithPath = $finder->withPathFilter(fn (string $path) => str_contains($path, 'FooExtendType.php'));

        $this->assertFoundClasses([
            TestFactory::class,
            GetterSetterType::class,
            FooType::class,
            MagicGetterSetterType::class,
            FooExtendType::class,
            NoTypeAnnotation::class,
            AbstractFooType::class,
            EnumType::class,
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