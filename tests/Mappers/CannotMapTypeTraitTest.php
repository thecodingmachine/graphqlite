<?php

namespace TheCodingMachine\GraphQLite\Mappers;

use ReflectionClass;
use TheCodingMachine\GraphQLite\AbstractQueryProviderTest;
use TheCodingMachine\GraphQLite\Fixtures\TestTypeId;
use TheCodingMachine\GraphQLite\Fixtures\Types\FooExtendType;

class CannotMapTypeTraitTest extends AbstractQueryProviderTest
{

    public function testAddParamInfo()
    {
        $e = CannotMapTypeException::createForType('Foo');
        $e->addParamInfo((new ReflectionClass('DateTime'))->getMethod('__construct')->getParameters()[0]);
        $e->addParamInfo((new ReflectionClass('DateTime'))->getMethod('__construct')->getParameters()[0]);

        $this->assertSame('For parameter $time, in DateTime::__construct, cannot map class "Foo" to a known GraphQL type. Check your TypeMapper configuration.', $e->getMessage());
    }

    public function testAddSourceFieldInfo()
    {
        $class = new ReflectionClass(TestTypeId::class);
        $sourceFields = $this->getAnnotationReader()->getSourceFields($class);

        $e = CannotMapTypeException::createForType('Foo');
        $e->addSourceFieldInfo($class, $sourceFields[0]);
        $e->addSourceFieldInfo($class, $sourceFields[0]);

        $this->assertSame('For @SourceField "test" declared in "TheCodingMachine\GraphQLite\Fixtures\TestTypeId", cannot map class "Foo" to a known GraphQL type. Check your TypeMapper configuration.', $e->getMessage());
    }

    public function testAddExtendTypeInfo()
    {
        $class = new ReflectionClass(FooExtendType::class);
        $extendType = $this->getAnnotationReader()->getExtendTypeAnnotation($class);

        $e = CannotMapTypeException::createForType('Foo');
        $e->addExtendTypeInfo($class, $extendType);
        $e->addExtendTypeInfo($class, $extendType);

        $this->assertSame('For @ExtendType(class="TheCodingMachine\GraphQLite\Fixtures\TestObject") annotation declared in class "TheCodingMachine\GraphQLite\Fixtures\Types\FooExtendType", cannot map class "Foo" to a known GraphQL type. Check your TypeMapper configuration.', $e->getMessage());
    }

    public function testAddReturnInfo()
    {
        $refMethod = (new ReflectionClass(__CLASS__))->getMethod('testAddReturnInfo');

        $e = CannotMapTypeException::createForType('Foo');
        $e->addReturnInfo($refMethod);
        $e->addReturnInfo($refMethod);

        $this->assertSame('For return type of TheCodingMachine\GraphQLite\Mappers\CannotMapTypeTraitTest::testAddReturnInfo, cannot map class "Foo" to a known GraphQL type. Check your TypeMapper configuration.', $e->getMessage());
    }
}
