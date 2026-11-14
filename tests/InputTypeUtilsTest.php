<?php

namespace TheCodingMachine\GraphQLite\Tests;

use PHPUnit\Runner\Version;
use ReflectionMethod;
use TheCodingMachine\GraphQLite\InputTypeUtils;
use TheCodingMachine\GraphQLite\MissingTypeHintRuntimeException;
use TheCodingMachine\GraphQLite\Tests\Fixtures\TestObject;
use TheCodingMachine\GraphQLite\Parameters\ExpandsInputTypeParameters;
use TheCodingMachine\GraphQLite\Parameters\InputTypeParameterInterface;
use TheCodingMachine\GraphQLite\Parameters\ParameterInterface;
use TheCodingMachine\GraphQLite\Parameters\SourceParameter;

class InputTypeUtilsTest extends AbstractQueryProvider
{

    public function testNoReturnType(): void
    {
        $inputTypeGenerator = $this->getInputTypeUtils();

        $this->expectException(MissingTypeHintRuntimeException::class);
        $this->expectExceptionMessage('Factory "TheCodingMachine\\GraphQLite\\Tests\\InputTypeUtilsTest::factoryNoReturnType" must have a return type.');
        $inputTypeGenerator->getInputTypeNameAndClassName(new ReflectionMethod($this, 'factoryNoReturnType'));
    }

    public function testInvalidReturnType(): void
    {
        $inputTypeGenerator = $this->getInputTypeUtils();

        $this->expectException(MissingTypeHintRuntimeException::class);
        $this->expectExceptionMessage('The return type of factory "TheCodingMachine\\GraphQLite\\Tests\\InputTypeUtilsTest::factoryStringReturnType" must be an object, "string" passed instead.');
        $inputTypeGenerator->getInputTypeNameAndClassName(new ReflectionMethod($this, 'factoryStringReturnType'));
    }

    public function testNullableReturnType(): void
    {
        $inputTypeGenerator = $this->getInputTypeUtils();

        $this->expectException(MissingTypeHintRuntimeException::class);
        $this->expectExceptionMessage('Factory "TheCodingMachine\\GraphQLite\\Tests\\InputTypeUtilsTest::factoryNullableReturnType" must have a non nullable return type.');
        $inputTypeGenerator->getInputTypeNameAndClassName(new ReflectionMethod($this, 'factoryNullableReturnType'));
    }

    public function testToInputParameters(): void
    {
        if (Version::series() === '8.5') {
            $this->markTestSkipped('Broken on PHPUnit 8.');
        }

        self::assertSame([], InputTypeUtils::toInputParameters([]));
        self::assertSame([
            'second' => $second = $this->createStub(InputTypeParameterInterface::class),
            'third' => $third = $this->createStub(InputTypeParameterInterface::class),
        ], InputTypeUtils::toInputParameters([
            'first' => new class ($second) implements ExpandsInputTypeParameters {
                public function __construct(
                    private readonly ParameterInterface $second,
                )
                {
                }

                public function toInputTypeParameters(): array
                {
                    return [
                        'second' => $this->second,
                    ];
                }
            },
            'third' => $third,
            'fourth' => $this->createStub(ParameterInterface::class),
        ]));
    }

    public function factoryNoReturnType()
    {

    }

    public function factoryStringReturnType(): string
    {
        return '';
    }

    public function factoryNullableReturnType(): ?TestObject
    {
        return null;
    }

}
