<?php

namespace TheCodingMachine\GraphQLite\Parameters;

use Generator;
use GraphQL\Deferred;
use GraphQL\Executor\Promise\Adapter\SyncPromiseAdapter;
use GraphQL\Executor\Promise\Promise;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use PHPUnit\Framework\TestCase;
use stdClass;
use TheCodingMachine\GraphQLite\Context\Context;
use TheCodingMachine\GraphQLite\Middlewares\MissingAuthorizationException;
use TheCodingMachine\GraphQLite\Security\AuthenticationServiceInterface;
use TheCodingMachine\GraphQLite\Types\ArgumentResolver;

class PrefetchDataParameterTest extends TestCase
{
    public function testResolveWithExistingResult(): void
    {
        $parameter = new PrefetchDataParameter('', function () {
            $this->fail('Should not be called');
        }, []);

        $source = new stdClass();
        $prefetchResult = new stdClass();
        $context = new Context();
        $args = [
            'first' => 'qwe',
            'second' => 'rty'
        ];
        $buffer = $context->getPrefetchBuffer($parameter);

        $buffer->storeResult($prefetchResult, $args);

        $resolvedParameterPromise = $parameter->resolve($source, $args, $context, $this->createStub(ResolveInfo::class));

        self::assertSame([$source], $buffer->getObjectsByArguments($args));
        self::assertSame($prefetchResult, $this->deferredValue($resolvedParameterPromise));
    }

    public function testResolveWithoutExistingResult(): void
    {
        $prefetchResult = new stdClass();
        $source = new stdClass();
        $prefetchHandler = function (array $sources, string $second) use ($prefetchResult, $source) {
            self::assertSame([$source], $sources);
            self::assertSame('rty', $second);

            return $prefetchResult;
        };

        $parameter = new PrefetchDataParameter('field', $prefetchHandler, [
            new InputTypeParameter(
                name: 'second',
                type: Type::string(),
                description: '',
                hasDefaultValue: false,
                defaultValue: null,
                argumentResolver: new ArgumentResolver()
            )
        ]);

        $context = new Context();
        $args = [
            'first' => 'qwe',
            'second' => 'rty',
        ];
        $buffer = $context->getPrefetchBuffer($parameter);

        $resolvedParameterPromise = $parameter->resolve($source, $args, $context, $this->createStub(ResolveInfo::class));

        self::assertFalse($buffer->hasResult($args));
        self::assertSame([$source], $buffer->getObjectsByArguments($args));
        self::assertSame($prefetchResult, $this->deferredValue($resolvedParameterPromise));
        self::assertTrue($buffer->hasResult($args));
    }

    private function deferredValue(Deferred $promise): mixed
    {
        $syncPromiseAdapter = new SyncPromiseAdapter();

        return $syncPromiseAdapter->wait(new Promise($promise, $syncPromiseAdapter));
    }
}