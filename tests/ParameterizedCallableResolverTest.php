<?php

namespace TheCodingMachine\GraphQLite;

use PHPUnit\Framework\Constraint\IsEqual;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\Contact;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Types\ExtendedContactOtherType;
use TheCodingMachine\GraphQLite\Fixtures\TestObject;
use TheCodingMachine\GraphQLite\Fixtures\Types\FooExtendType;
use TheCodingMachine\GraphQLite\Parameters\ParameterInterface;

class ParameterizedCallableResolverTest extends TestCase
{
    public function testResolveReturnsCallableAndParametersFromStaticMethod(): void
    {
        $expectedParameters = [$this->createStub(ParameterInterface::class)];

        $fieldsBuilder = $this->createMock(FieldsBuilder::class);
        $fieldsBuilder->method('getParameters')
            ->with(new IsEqual(new \ReflectionMethod(Contact::class, 'prefetchTheContacts')), 123)
            ->willReturn($expectedParameters);

        [$resultingCallable, $resultingParameters] = (new ParameterizedCallableResolver(
            $fieldsBuilder,
            $this->createMock(ContainerInterface::class),
        ))->resolve([Contact::class, 'prefetchTheContacts'], self::class, 123);

        self::assertSame(['test'], $resultingCallable(['test']));
        self::assertSame($expectedParameters, $resultingParameters);
    }

    public function testResolveReturnsCallableAndParametersFromStaticMethodOnSelf(): void
    {
        $expectedParameters = [$this->createStub(ParameterInterface::class)];

        $fieldsBuilder = $this->createMock(FieldsBuilder::class);
        $fieldsBuilder->method('getParameters')
            ->with(new IsEqual(new \ReflectionMethod(Contact::class, 'prefetchTheContacts')), 123)
            ->willReturn($expectedParameters);

        [$resultingCallable, $resultingParameters] = (new ParameterizedCallableResolver(
            $fieldsBuilder,
            $this->createMock(ContainerInterface::class),
        ))->resolve('prefetchTheContacts', Contact::class, 123);

        self::assertSame(['test'], $resultingCallable(['test']));
        self::assertSame($expectedParameters, $resultingParameters);
    }

    public function testResolveReturnsCallableAndParametersFromContainer(): void
    {
        $expectedParameters = [$this->createStub(ParameterInterface::class)];

        $fieldsBuilder = $this->createMock(FieldsBuilder::class);
        $fieldsBuilder->method('getParameters')
            ->with(new IsEqual(new \ReflectionMethod(FooExtendType::class, 'customExtendedField')), 123)
            ->willReturn($expectedParameters);

        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')
            ->with(FooExtendType::class)
            ->willReturn(new FooExtendType());

        [$resultingCallable, $resultingParameters] = (new ParameterizedCallableResolver(
            $fieldsBuilder,
            $container,
        ))->resolve([FooExtendType::class, 'customExtendedField'], self::class, 123);

        self::assertSame('TEST', $resultingCallable(new TestObject('test')));
        self::assertSame($expectedParameters, $resultingParameters);
    }

    public function testResolveThrowsInvalidCallableMethodNotFoundException(): void
    {
        $this->expectException(InvalidCallableRuntimeException::class);
        $this->expectExceptionMessage('Method TheCodingMachine\\GraphQLite\\ParameterizedCallableResolverTest::doesntExist wasn\'t found or isn\'t accessible.');

        (new ParameterizedCallableResolver(
            $this->createMock(FieldsBuilder::class),
            $this->createMock(ContainerInterface::class),
        ))->resolve('doesntExist', self::class);
    }
}