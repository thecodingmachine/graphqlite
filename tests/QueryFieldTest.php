<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use GraphQL\Error\Error;
use GraphQL\Executor\Promise\Adapter\SyncPromise;
use GraphQL\Executor\Promise\Adapter\SyncPromiseAdapter;
use GraphQL\Executor\Promise\Promise;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use TheCodingMachine\GraphQLite\Fixtures\TestObject;
use TheCodingMachine\GraphQLite\Middlewares\ServiceResolver;
use TheCodingMachine\GraphQLite\Middlewares\SourceMethodResolver;
use TheCodingMachine\GraphQLite\Parameters\InputTypeParameter;
use TheCodingMachine\GraphQLite\Parameters\ParameterInterface;
use TheCodingMachine\GraphQLite\Types\ArgumentResolver;

class QueryFieldTest extends TestCase
{
    public function testExceptionsHandling(): void
    {
        $sourceResolver = new SourceMethodResolver(new ReflectionMethod(TestObject::class, 'getTest'));
        $queryField = new QueryField('foo', Type::string(), [
            new class implements ParameterInterface {
                public function resolve(?object $source, array $args, mixed $context, ResolveInfo $info): mixed
                {
                    throw new Error('boum');
                }
            },
        ], $sourceResolver, $sourceResolver, null, null, []);

        $resolve = $queryField->resolveFn;

        $this->expectException(Error::class);
        $resolve(new TestObject('foo'), ['arg' => 12], null, $this->createMock(ResolveInfo::class));
    }

    public function testParametersDescription(): void
    {
        $sourceResolver = new ServiceResolver(static fn () => null);
        $queryField = new QueryField('foo', Type::string(), [
            'arg' => new InputTypeParameter('arg', Type::string(), 'Foo argument', false, null, new ArgumentResolver()),
        ], $sourceResolver, $sourceResolver, null, null, []);

        $this->assertEquals('Foo argument', $queryField->args[0]->description);
    }

    public function testWrapsClosureInDeferred(): void
    {
        $sourceResolver = new ServiceResolver(static fn () => function () {
            return 123;
        });
        $queryField = new QueryField('foo', Type::string(), [], $sourceResolver, $sourceResolver, null, null, []);

        $deferred = ($queryField->resolveFn)(null, [], null, $this->createStub(ResolveInfo::class));

        $this->assertInstanceOf(SyncPromise::class, $deferred);

        $syncPromiseAdapter = new SyncPromiseAdapter();
        $syncPromiseAdapter->wait(new Promise($deferred, $syncPromiseAdapter));

        $this->assertSame(123, $deferred->result);
    }
}
