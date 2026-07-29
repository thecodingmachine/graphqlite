<?php

namespace TheCodingMachine\GraphQLite;

use Closure;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionFunction;
use ReflectionMethod;
use TheCodingMachine\GraphQLite\Containers\EmptyContainer;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\Contact;
use TheCodingMachine\GraphQLite\Fixtures\TestObject;
use TheCodingMachine\GraphQLite\Fixtures\Types\FooExtendType;

class CallableResolverTest extends TestCase
{
    public function testResolvesArrayCallableNamingAStaticMethod(): void
    {
        [$closure, $reflection] = $this->resolver()->resolve([Contact::class, 'prefetchTheContacts']);

        self::assertInstanceOf(Closure::class, $closure);
        self::assertSame(['test'], $closure(['test']));
        self::assertInstanceOf(ReflectionMethod::class, $reflection);
        self::assertSame('prefetchTheContacts', $reflection->getName());
    }

    public function testResolvesBareMethodNameAgainstTheClassContext(): void
    {
        [$closure, $reflection] = $this->resolver()->resolve('prefetchTheContacts', Contact::class);

        self::assertSame(['test'], $closure(['test']));
        self::assertSame(Contact::class, $reflection->getDeclaringClass()->getName());
    }

    public function testBareMethodNameWithoutClassContextIsRejected(): void
    {
        $this->expectException(InvalidCallableRuntimeException::class);
        $this->expectExceptionMessage('The bare method name "prefetchTheContacts" cannot be resolved because no class context was provided.');

        $this->resolver()->resolve('prefetchTheContacts');
    }

    /**
     * The case Closure::fromCallable() cannot handle on its own: it throws a TypeError for a
     * non-static method, so the container has to be consulted instead.
     */
    public function testResolvesNonStaticMethodThroughTheContainer(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
            ->method('get')
            ->with(FooExtendType::class)
            ->willReturn(new FooExtendType());

        [$closure, $reflection] = (new CallableResolver($container))->resolve([FooExtendType::class, 'customExtendedField']);

        self::assertSame('TEST', $closure(new TestObject('test')));
        self::assertSame('customExtendedField', $reflection->getName());
    }

    /**
     * The container must not be touched while the schema is being built, only when the rule runs.
     */
    public function testContainerIsNotConsultedUntilTheClosureIsInvoked(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->never())->method('get');

        (new CallableResolver($container))->resolve([FooExtendType::class, 'customExtendedField']);
    }

    public function testResolvesInvokableObject(): void
    {
        $invokable = new class {
            public function __invoke(string $value): string
            {
                return 'invoked:' . $value;
            }
        };

        [$closure, $reflection] = $this->resolver()->resolve($invokable);

        self::assertInstanceOf(Closure::class, $closure);
        self::assertSame('invoked:x', $closure('x'));
        self::assertInstanceOf(ReflectionMethod::class, $reflection);
        self::assertSame('__invoke', $reflection->getName());
    }

    public function testInvokableObjectKeepsItsConstructorState(): void
    {
        $invokable = new class ('expected') {
            public function __construct(private readonly string $expected)
            {
            }

            public function __invoke(string $value): bool
            {
                return $value === $this->expected;
            }
        };

        [$closure] = $this->resolver()->resolve($invokable);

        self::assertTrue($closure('expected'));
        self::assertFalse($closure('other'));
    }

    public function testResolvesClosureUnchanged(): void
    {
        $closure = static fn (string $value): string => 'closure:' . $value;

        [$resolved, $reflection] = $this->resolver()->resolve($closure);

        self::assertSame($closure, $resolved);
        self::assertInstanceOf(ReflectionFunction::class, $reflection);
    }

    /**
     * A Closure produced by Closure::fromCallable(), which is what first-class callable syntax
     * yields on PHP 8.5, still knows the method it came from. Callers that need to reflect the
     * target, such as ParameterizedCallableResolver, therefore keep working.
     */
    public function testClosureFromACallableReflectsBackToItsMethod(): void
    {
        $closure = Closure::fromCallable([Contact::class, 'prefetchTheContacts']);

        [, $reflection] = $this->resolver()->resolve($closure);

        self::assertInstanceOf(ReflectionMethod::class, $reflection);
        self::assertSame('prefetchTheContacts', $reflection->getName());
        self::assertSame(Contact::class, $reflection->getDeclaringClass()->getName());
    }

    public function testUnknownMethodIsRejectedAtResolutionTime(): void
    {
        $this->expectException(InvalidCallableRuntimeException::class);
        $this->expectExceptionMessage('Method ' . Contact::class . '::doesntExist wasn\'t found or isn\'t accessible.');

        $this->resolver()->resolve([Contact::class, 'doesntExist']);
    }

    public function testNonPublicMethodIsRejectedAtResolutionTime(): void
    {
        $this->expectException(InvalidCallableRuntimeException::class);
        $this->expectExceptionMessage('must be public to be used as a callable');

        $this->resolver()->resolve([self::class, 'hiddenRule']);
    }

    public function testNonInvokableObjectIsRejected(): void
    {
        $this->expectException(InvalidCallableRuntimeException::class);
        $this->expectExceptionMessage('has no __invoke() method');

        $this->resolver()->resolve(new TestObject('test'));
    }

    private function resolver(): CallableResolver
    {
        return new CallableResolver(new EmptyContainer());
    }

    private static function hiddenRule(): bool
    {
        return true;
    }
}

