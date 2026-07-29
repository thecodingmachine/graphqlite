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
            new CallableResolver($this->createMock(ContainerInterface::class)),
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
            new CallableResolver($this->createMock(ContainerInterface::class)),
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
        $container->expects($this->once())
            ->method('get')
            ->with(FooExtendType::class)
            ->willReturn(new FooExtendType());

        [$resultingCallable, $resultingParameters] = (new ParameterizedCallableResolver(
            $fieldsBuilder,
            new CallableResolver($container),
        ))->resolve([FooExtendType::class, 'customExtendedField'], self::class, 123);

        self::assertSame('TEST', $resultingCallable(new TestObject('test')));
        self::assertSame($expectedParameters, $resultingParameters);
    }

    /**
     * A Closure that came from a method keeps that origin, so it can still be parameter-mapped.
     * This is what first-class callable syntax produces on PHP 8.5.
     */
    public function testResolveAcceptsAClosureBackedByAMethod(): void
    {
        $expectedParameters = [$this->createStub(ParameterInterface::class)];

        $fieldsBuilder = $this->createMock(FieldsBuilder::class);
        $fieldsBuilder->method('getParameters')
            ->with(new IsEqual(new \ReflectionMethod(Contact::class, 'prefetchTheContacts')), 0)
            ->willReturn($expectedParameters);

        [$resultingCallable, $resultingParameters] = (new ParameterizedCallableResolver(
            $fieldsBuilder,
            new CallableResolver($this->createMock(ContainerInterface::class)),
        ))->resolve(\Closure::fromCallable([Contact::class, 'prefetchTheContacts']), self::class);

        self::assertSame(['test'], $resultingCallable(['test']));
        self::assertSame($expectedParameters, $resultingParameters);
    }

    /**
     * An anonymous closure has no originating method, so its parameters cannot become GraphQL
     * arguments. The message stays generic: this resolver does not know which attribute called it.
     */
    public function testResolveRejectsAClosureWithNoOriginatingMethod(): void
    {
        $this->expectException(InvalidCallableRuntimeException::class);
        $this->expectExceptionMessage('The callable must name a real method, because its parameters are mapped to GraphQL arguments');

        (new ParameterizedCallableResolver(
            $this->createMock(FieldsBuilder::class),
            new CallableResolver($this->createMock(ContainerInterface::class)),
        ))->resolve(static fn (array $sources): array => $sources, self::class);
    }

    public function testResolveThrowsInvalidCallableMethodNotFoundException(): void
    {
        $this->expectException(InvalidCallableRuntimeException::class);
        $this->expectExceptionMessage('Method TheCodingMachine\\GraphQLite\\ParameterizedCallableResolverTest::doesntExist wasn\'t found or isn\'t accessible.');

        (new ParameterizedCallableResolver(
            $this->createMock(FieldsBuilder::class),
            new CallableResolver($this->createMock(ContainerInterface::class)),
        ))->resolve('doesntExist', self::class);
    }
}