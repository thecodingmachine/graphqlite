<?php

namespace TheCodingMachine\GraphQLite\Utils\Namespaces;

use Mouf\Composer\ClassNameMapper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;
use TheCodingMachine\GraphQLite\Fixtures\Types\AbstractFooType;
use TheCodingMachine\GraphQLite\Fixtures\Types\FooExtendType;
use TheCodingMachine\GraphQLite\Fixtures\Types\FooType;
use TheCodingMachine\GraphQLite\Fixtures\Types\GetterSetterType;
use TheCodingMachine\GraphQLite\Fixtures\Types\MagicGetterSetterType;
use TheCodingMachine\GraphQLite\Fixtures\Types\NoTypeAnnotation;
use TheCodingMachine\GraphQLite\Fixtures\Types\TestFactory;

class NSTest extends TestCase
{
    /**
     * @dataProvider loadsClassListProvider
     */
    public function testLoadsClassList(array $expectedClasses, string $namespace, bool $autoload): void
    {
        $ns = new NS(
            namespace: $namespace,
            cache: new Psr16Cache(new ArrayAdapter()),
            classNameMapper: ClassNameMapper::createFromComposerFile(null, null, true),
            globTTL: null,
            recursive: true,
            autoload: $autoload,
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
            true,
        ];

        // The class should be ignored.
        yield 'incorrect namespace class without autoload' => [
            [],
            'TheCodingMachine\GraphQLite\Fixtures\BadNamespace',
            false,
        ];
    }
}