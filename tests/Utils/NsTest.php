<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Utils;

use Exception;
use Kcs\ClassFinder\Finder\ComposerFinder;
use Kcs\ClassFinder\Finder\FinderInterface;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;
use TheCodingMachine\GraphQLite\Fixtures\Types\AbstractFooType;
use TheCodingMachine\GraphQLite\Fixtures\Types\EnumType;
use TheCodingMachine\GraphQLite\Fixtures\Types\FooExtendType;
use TheCodingMachine\GraphQLite\Fixtures\Types\FooType;
use TheCodingMachine\GraphQLite\Fixtures\Types\GetterSetterType;
use TheCodingMachine\GraphQLite\Fixtures\Types\MagicGetterSetterType;
use TheCodingMachine\GraphQLite\Fixtures\Types\NoTypeAnnotation;
use TheCodingMachine\GraphQLite\Fixtures\Types\TestFactory;
use TheCodingMachine\GraphQLite\Utils\Namespaces\NS;

use function array_keys;

class NsTest extends TestCase
{
    private CacheInterface $cache;
    private string $namespace;
    private FinderInterface $finder;
    private int $globTTL;
    protected function setUp(): void
    {
        $this->cache = new Psr16Cache(new ArrayAdapter());
        $this->namespace = 'TheCodingMachine\GraphQLite\Fixtures\Types';
        $this->finder = new ComposerFinder();
        $this->globTTL = 10;
    }

    /** @dataProvider loadsClassListProvider */
    public function testLoadsClassList(array $expectedClasses, string $namespace): void
    {
        $ns = new NS(
            namespace: $namespace,
            cache: $this->cache,
            finder: $this->finder,
            globTTL: null,
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
                EnumType::class
            ],
            'TheCodingMachine\GraphQLite\Fixtures\Types',
        ];

        // The class should be ignored.
        yield 'incorrect namespace class without autoload' => [
            [],
            'TheCodingMachine\GraphQLite\Fixtures\BadNamespace',
        ];
    }

    public function testCaching(): void
    {
        $ns = new NS(
            namespace: $this->namespace,
            cache: $this->cache,
            finder: $this->finder,
            globTTL: $this->globTTL,
        );
        self::assertNotNull($ns->getClassList());

        // create with mock finder to test cache
        $finder = $this->createMock(FinderInterface::class);
        $finder->expects(self::never())->method('inNamespace')->willReturnSelf();
        $ns = new NS(
            namespace: $this->namespace,
            cache: $this->cache,
            finder: $finder,
            globTTL: $this->globTTL,
        );
        self::assertNotNull($ns->getClassList());
    }

    public function testCachingWithInvalidKey(): void
    {
        $exception = new class extends Exception implements InvalidArgumentException {
        };
        $cache = $this->createMock(CacheInterface::class);
        $cache->expects(self::once())->method('get')->willThrowException($exception);
        $cache->expects(self::once())->method('set')->willThrowException($exception);
        $ns = new NS(
            namespace: $this->namespace,
            cache: $cache,
            finder: $this->finder,
            globTTL: $this->globTTL,
        );
        $ns->getClassList();
    }

    public function testCachingWithInvalidCache(): void
    {
        $cache = $this->createMock(CacheInterface::class);
        $cache->expects(self::once())->method('get')->willReturn(['foo']);
        $ns = new NS(
            namespace: $this->namespace,
            cache: $cache,
            finder: $this->finder,
            globTTL: $this->globTTL,
        );
        $classList = $ns->getClassList();
        self::assertNotNull($classList);
        self::assertNotEmpty($classList);
    }

    public function testFinderWithUnexpectedOutput() {

        $finder = $this->createMock(FinderInterface::class);
        $finder->expects(self::once())->method('inNamespace')->willReturnSelf();
        $finder->expects(self::once())->method('getIterator')->willReturn(new \ArrayIterator([ 'test' => new \ReflectionException()]));
        $ns = new NS(
            namespace: $this->namespace,
            cache: $this->cache,
            finder: $finder,
            globTTL: $this->globTTL,
        );
        $classList = $ns->getClassList();
        self::assertNotNull($classList);
        self::assertEmpty($classList);}
}
