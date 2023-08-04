<?php

namespace TheCodingMachine\GraphQLite\Mappers\Parameters;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Type;
use PHPUnit\Framework\Constraint\IsEqual;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use TheCodingMachine\GraphQLite\AbstractQueryProviderTest;
use TheCodingMachine\GraphQLite\Annotations\Autowire;
use TheCodingMachine\GraphQLite\Annotations\ParameterAnnotations;
use TheCodingMachine\GraphQLite\Annotations\Prefetch;
use TheCodingMachine\GraphQLite\FieldsBuilder;
use TheCodingMachine\GraphQLite\Fixtures\TestType;
use TheCodingMachine\GraphQLite\InvalidCallableRuntimeException;
use TheCodingMachine\GraphQLite\InvalidPrefetchMethodRuntimeException;
use TheCodingMachine\GraphQLite\ParameterizedCallableResolver;
use TheCodingMachine\GraphQLite\Parameters\ParameterInterface;
use TheCodingMachine\GraphQLite\Parameters\PrefetchDataParameter;

class PrefetchParameterMiddlewareTest extends AbstractQueryProviderTest
{
    public function testIgnoresParametersWithoutPrefetchAttribute(): void
    {
        $expected = new HardCodedParameter();

        $next = $this->createMock(ParameterHandlerInterface::class);
        $next->method('mapParameter')->willReturn($expected);

        $refMethod = new ReflectionMethod(__CLASS__, 'dummy');
        $parameter = $refMethod->getParameters()[0];

        $result = (new PrefetchParameterMiddleware(
            $this->createMock(ParameterizedCallableResolver::class),
        ))->mapParameter(
            $parameter,
            new DocBlock(),
            null,
            new ParameterAnnotations([]),
            $next,
        );

        self::assertSame($expected, $result);
    }

    public function testMapsToPrefetchDataParameter(): void
    {
        $parameterizedCallableResolver = $this->createMock(ParameterizedCallableResolver::class);
        $parameterizedCallableResolver
            ->method('resolve')
            ->with('dummy', new IsEqual(new ReflectionClass(self::class)), 1)
            ->willReturn([
                fn() => null,
                [],
            ]);

        $refMethod = new ReflectionMethod(__CLASS__, 'dummy');
        $parameter = $refMethod->getParameters()[0];

        $result = (new PrefetchParameterMiddleware(
            $parameterizedCallableResolver,
        ))->mapParameter(
            $parameter,
            new DocBlock(),
            null,
            new ParameterAnnotations([
                new Prefetch('dummy'),
            ]),
            $this->createMock(ParameterHandlerInterface::class),
        );

        self::assertInstanceOf(PrefetchDataParameter::class, $result);
    }

    public function testRethrowsInvalidCallableAsInvalidPrefetchException(): void
    {
        $this->expectException(InvalidPrefetchMethodRuntimeException::class);
        $this->expectExceptionMessage('#[Prefetch] attribute on parameter $foo in TheCodingMachine\\GraphQLite\\Mappers\\Parameters\\PrefetchParameterMiddlewareTest::dummy specifies a callable that is invalid: Method TheCodingMachine\\GraphQLite\\Fixtures\\TestType::notExists wasn\'t found or isn\'t accessible.');

        $parameterizedCallableResolver = $this->createMock(ParameterizedCallableResolver::class);
        $parameterizedCallableResolver
            ->method('resolve')
            ->with([TestType::class, 'notExists'], new IsEqual(new ReflectionClass(self::class)), 1)
            ->willThrowException(InvalidCallableRuntimeException::methodNotFound(TestType::class, 'notExists'));

        $refMethod = new ReflectionMethod(__CLASS__, 'dummy');
        $parameter = $refMethod->getParameters()[0];

        (new PrefetchParameterMiddleware(
            $parameterizedCallableResolver,
        ))->mapParameter(
            $parameter,
            new DocBlock(),
            null,
            new ParameterAnnotations([
                new Prefetch([TestType::class, 'notExists']),
            ]),
            $this->createMock(ParameterHandlerInterface::class),
        );
    }

    private function dummy($foo)
    {

    }
}
