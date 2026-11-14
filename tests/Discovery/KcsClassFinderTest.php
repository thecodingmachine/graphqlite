<?php

namespace TheCodingMachine\GraphQLite\Tests\Discovery;

use Kcs\ClassFinder\Finder\ComposerFinder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use TheCodingMachine\GraphQLite\Discovery\ClassFinder;
use TheCodingMachine\GraphQLite\Discovery\KcsClassFinder;
use TheCodingMachine\GraphQLite\Tests\Fixtures\TestType;
use TheCodingMachine\GraphQLite\Tests\Fixtures\Types\AbstractFooType;
use TheCodingMachine\GraphQLite\Tests\Fixtures\Types\EnumType;
use TheCodingMachine\GraphQLite\Tests\Fixtures\Types\FooExtendType;
use TheCodingMachine\GraphQLite\Tests\Fixtures\Types\FooType;
use TheCodingMachine\GraphQLite\Tests\Fixtures\Types\GetterSetterType;
use TheCodingMachine\GraphQLite\Tests\Fixtures\Types\MagicGetterSetterType;
use TheCodingMachine\GraphQLite\Tests\Fixtures\Types\NoTypeAnnotation;
use TheCodingMachine\GraphQLite\Tests\Fixtures\Types\TestFactory;

#[CoversClass(KcsClassFinder::class)]
class KcsClassFinderTest extends TestCase
{
    public function testYieldsGivenClasses(): void
    {
        $namespaces = 'TheCodingMachine\GraphQLite\Tests\Fixtures\Types';
        $finder = new KcsClassFinder(
            (new ComposerFinder())->inNamespace($namespaces),
            md5($namespaces)
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
