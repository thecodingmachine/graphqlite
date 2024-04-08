<?php

namespace TheCodingMachine\GraphQLite\Utils;

use Exception;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\Contact;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\Post;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\Preferences;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\Product;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\TrickyProduct;
use TheCodingMachine\GraphQLite\Fixtures\Types\GetterSetterType;
use TheCodingMachine\GraphQLite\Fixtures\Types\MagicGetterSetterType;

class PropertyAccessorTest extends TestCase
{
    #[DataProvider('findGetterProvider')]
    public function testFindGetter(mixed $expected, string $class, string $propertyName): void
    {
        self::assertSame($expected, PropertyAccessor::findGetter($class, $propertyName));
    }

    public static function findGetterProvider(): iterable
    {
        yield 'regular property' => [null, MagicGetterSetterType::class, 'one'];
        yield 'getter' => ['getTwo', MagicGetterSetterType::class, 'two'];
        yield 'isser' => ['isThree', MagicGetterSetterType::class, 'three'];
        yield 'private getter' => [null, MagicGetterSetterType::class, 'four'];
        yield 'undefined property' => [null, MagicGetterSetterType::class, 'twenty'];
    }

    #[DataProvider('findSetterProvider')]
    public function testFindSetter(mixed $expected, string $class, string $propertyName): void
    {
        self::assertSame($expected, PropertyAccessor::findSetter($class, $propertyName));
    }

    public static function findSetterProvider(): iterable
    {
        yield 'regular property' => [null, MagicGetterSetterType::class, 'one'];
        yield 'setter' => ['setTwo', MagicGetterSetterType::class, 'two'];
        yield 'private setter' => [null, MagicGetterSetterType::class, 'four'];
        yield 'undefined property' => [null, MagicGetterSetterType::class, 'twenty'];
    }

    #[DataProvider('getValueProvider')]
    public function testGetValue(mixed $expected, object $object, string $propertyName, array $args = []): void
    {
        if ($expected instanceof Exception) {
            $this->expectExceptionObject($expected);
        }

        self::assertSame($expected, PropertyAccessor::getValue($object, $propertyName, ...$args));
    }

    public static function getValueProvider(): iterable
    {
        yield 'regular property' => ['result', new MagicGetterSetterType(one: 'result'), 'one'];
        yield 'getter' => ['result', new MagicGetterSetterType(), 'two', ['result']];
        yield 'isser #1' => [true, new MagicGetterSetterType(), 'three', ['foo']];
        yield 'isser #2' => [false, new MagicGetterSetterType(), 'three', ['bar']];
        yield 'private getter' => ['result', new MagicGetterSetterType(four: 'result'), 'four'];
        yield 'magic getter' => ['magic', new MagicGetterSetterType(), 'twenty'];
        yield 'undefined property' => [AccessPropertyException::createForUnreadableProperty(GetterSetterType::class, 'twenty'), new GetterSetterType(), 'twenty'];
    }

    #[DataProvider('setValueProvider')]
    public function testSetValue(mixed $expected, object $object, string $propertyName, mixed $value): void
    {
        if ($expected instanceof Exception) {
            $this->expectExceptionObject($expected);
        }

        PropertyAccessor::setValue($object, $propertyName, $value);

        self::assertSame($expected, $object->{$propertyName});
    }

    public static function setValueProvider(): iterable
    {
        yield 'regular property' => ['result', new MagicGetterSetterType(one: 'result'), 'one', 'result'];
        yield 'setter' => ['result set', new MagicGetterSetterType(), 'two', 'result'];
        yield 'private setter' => ['result', new MagicGetterSetterType(four: 'result'), 'four', 'result'];
        yield 'magic setter' => ['magic', new MagicGetterSetterType(), 'twenty', 'result'];
        yield 'undefined property' => [AccessPropertyException::createForUnwritableProperty(GetterSetterType::class, 'twenty'), new GetterSetterType(), 'twenty', 'result'];
    }
}