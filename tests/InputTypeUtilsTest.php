<?php

namespace TheCodingMachine\GraphQLite;

use ReflectionMethod;
use TheCodingMachine\GraphQLite\Fixtures\TestObject;

class InputTypeUtilsTest extends AbstractQueryProviderTest
{

    public function testNoReturnType(): void
    {
        $inputTypeGenerator = $this->getInputTypeUtils();

        $this->expectException(MissingTypeHintRuntimeException::class);
        $this->expectExceptionMessage('Factory "TheCodingMachine\\GraphQLite\\InputTypeUtilsTest::factoryNoReturnType" must have a return type.');
        $inputTypeGenerator->getInputTypeNameAndClassName(new ReflectionMethod($this, 'factoryNoReturnType'));
    }

    public function testInvalidReturnType(): void
    {
        $inputTypeGenerator = $this->getInputTypeUtils();

        $this->expectException(MissingTypeHintRuntimeException::class);
        $this->expectExceptionMessage('The return type of factory "TheCodingMachine\\GraphQLite\\InputTypeUtilsTest::factoryStringReturnType" must be an object, "string" passed instead.');
        $inputTypeGenerator->getInputTypeNameAndClassName(new ReflectionMethod($this, 'factoryStringReturnType'));
    }

    public function testNullableReturnType(): void
    {
        $inputTypeGenerator = $this->getInputTypeUtils();

        $this->expectException(MissingTypeHintRuntimeException::class);
        $this->expectExceptionMessage('Factory "TheCodingMachine\\GraphQLite\\InputTypeUtilsTest::factoryNullableReturnType" must have a non nullable return type.');
        $inputTypeGenerator->getInputTypeNameAndClassName(new ReflectionMethod($this, 'factoryNullableReturnType'));
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
