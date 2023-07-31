<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Parameters;

use GraphQL\Type\Definition\ResolveInfo;
use TheCodingMachine\GraphQLite\Context\ContextInterface;
use TheCodingMachine\GraphQLite\GraphQLRuntimeException;
use TheCodingMachine\GraphQLite\Middlewares\ResolverInterface;
use TheCodingMachine\GraphQLite\PrefetchBuffer;
use TheCodingMachine\GraphQLite\QueryField;

use function array_unshift;
use function assert;
use function is_callable;

/**
 * Typically the first parameter of "self" fields or the second parameter of "external" fields that will be filled with the data fetched from the prefetch method.
 */
class PrefetchDataParameter implements ParameterInterface
{
    /** @param array<string, ParameterInterface> $parameters Indexed by argument name. */
    public function __construct(
        private readonly string $fieldName,
        private readonly ResolverInterface $originalResolver,
        private readonly string $methodName,
        private readonly array $parameters,
    )
    {
    }

    /** @param array<string, mixed> $args */
    public function resolve(object|null $source, array $args, mixed $context, ResolveInfo $info): mixed
    {
        // The PrefetchBuffer must be tied to the current request execution. The only object we have for this is $context
        // $context MUST be a ContextInterface
        if (! $context instanceof ContextInterface) {
            throw new GraphQLRuntimeException('When using "prefetch", you sure ensure that the GraphQL execution "context" (passed to the GraphQL::executeQuery method) is an instance of \TheCodingMachine\GraphQLite\Context\Context');
        }

        $prefetchBuffer = $context->getPrefetchBuffer($this);

        if (! $prefetchBuffer->hasResult($args)) {
            $prefetchResult = $this->computePrefetch($source, $args, $context, $info, $prefetchBuffer);

            $prefetchBuffer->storeResult($prefetchResult, $args);
        }

        return $prefetchResult ?? $prefetchBuffer->getResult($args);
    }

    /** @param array<string, mixed> $args */
    private function computePrefetch(object|null $source, array $args, mixed $context, ResolveInfo $info, PrefetchBuffer $prefetchBuffer): mixed
    {
        // TODO: originalPrefetchResolver and prefetchResolver needed!!!
        $prefetchCallable = [
            $this->originalResolver->executionSource($source),
            $this->methodName,
        ];

        $sources = $prefetchBuffer->getObjectsByArguments($args);

        assert(is_callable($prefetchCallable));
        $toPassPrefetchArgs = QueryField::paramsToArguments($this->fieldName, $this->parameters, $source, $args, $context, $info, $prefetchCallable);

        array_unshift($toPassPrefetchArgs, $sources);
        assert(is_callable($prefetchCallable));

        return $prefetchCallable(...$toPassPrefetchArgs);
    }
}
