<?php

namespace TheCodingMachine\GraphQLite\Utils;

use Kcs\ClassFinder\Finder\ComposerFinder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;
use TheCodingMachine\GraphQLite\Utils\Namespaces\NS;
use TheCodingMachine\GraphQLite\Fixtures\Types\AbstractFooType;
use TheCodingMachine\GraphQLite\Fixtures\Types\FooExtendType;
use TheCodingMachine\GraphQLite\Fixtures\Types\FooType;
use TheCodingMachine\GraphQLite\Fixtures\Types\GetterSetterType;
use TheCodingMachine\GraphQLite\Fixtures\Types\MagicGetterSetterType;
use TheCodingMachine\GraphQLite\Fixtures\Types\NoTypeAnnotation;
use TheCodingMachine\GraphQLite\Fixtures\Types\TestFactory;

class NsTest extends TestCase
{
    /**
     * @dataProvider loadsClassListProvider
     */
    public function testLoadsClassList(array $expectedClasses, string $namespace): void
    {
        $ns = new NS(
            namespace: $namespace,
            cache: new Psr16Cache(new ArrayAdapter()),
            finder: new ComposerFinder(),
            globTTL: null
        );

        self::assertEqualsCanonicalizing($expectedClasses, array_keys($ns->getClassList()));
    }

    public static function loadsClassListProvider(): iterable
    {
        yield 'autoload' => [
            [
                TestFactory::class,
                GetterSetterType::class,
                FooType::class,
                MagicGetterSetterType::class,
                FooExtendType::class,
                NoTypeAnnotation::class,
                AbstractFooType::class,
            ],
            'TheCodingMachine\GraphQLite\Fixtures\Types',
        ];

        // The class should be ignored.
        yield 'incorrect namespace class without autoload' => [
            [],
            'TheCodingMachine\GraphQLite\Fixtures\BadNamespace',
        ];
    }
}