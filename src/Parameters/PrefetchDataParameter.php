<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Parameters;

use GraphQL\Deferred;
use GraphQL\Type\Definition\ResolveInfo;
use TheCodingMachine\GraphQLite\Context\ContextInterface;
use TheCodingMachine\GraphQLite\GraphQLRuntimeException;
use TheCodingMachine\GraphQLite\InputTypeUtils;
use TheCodingMachine\GraphQLite\PrefetchBuffer;
use TheCodingMachine\GraphQLite\QueryField;

use function assert;

/**
 * Typically the first parameter of "self" fields or the second parameter of "external" fields that will be filled with the data fetched from the prefetch method.
 */
class PrefetchDataParameter implements ParameterInterface, ExpandsInputTypeParameters
{
    /**
     * @param callable $resolver
     * @param array<string, ParameterInterface> $parameters Indexed by argument name.
     */
    public function __construct(
        private readonly string $fieldName,
        private readonly mixed $resolver,
        public readonly array $parameters,
    )
    {
    }

    /** @param array<string, mixed> $args */
    public function resolve(object|null $source, array $args, mixed $context, ResolveInfo $info): Deferred
    {
        assert($source !== null);

        // The PrefetchBuffer must be tied to the current request execution. The only object we have for this is $context
        // $context MUST be a ContextInterface
        if (! $context instanceof ContextInterface) {
            throw new GraphQLRuntimeException('When using "prefetch", you should ensure that the GraphQL execution "context" (passed to the GraphQL::executeQuery method) is an instance of \TheCodingMachine\GraphQLite\Context\Context');
        }

        $prefetchBuffer = $context->getPrefetchBuffer($this);
        $prefetchBuffer->register($source, $args);

        // The way this works is simple: GraphQL first iterates over every requested field and calls ->resolve()
        // on it. That, in turn, calls this method. GraphQL doesn't need the actual value just yet; it simply
        // calls ->resolve to let developers do complex value fetching.
        //
        // So we record all of these ->resolve() calls, collect them together and when a value is actually
        // needed, GraphQL calls the callback of Deferred below. That's when we call the prefetch method,
        // already knowing all the requested fields (source-arguments combinations).
        return new Deferred(function () use ($info, $context, $args, $prefetchBuffer) {
            if (! $prefetchBuffer->hasResult($args)) {
                $prefetchResult = $this->computePrefetch($args, $context, $info, $prefetchBuffer);

                $prefetchBuffer->storeResult($prefetchResult, $args);
            }

            return $prefetchResult ?? $prefetchBuffer->getResult($args);
        });
    }

    /** @param array<string, mixed> $args */
    private function computePrefetch(array $args, mixed $context, ResolveInfo $info, PrefetchBuffer $prefetchBuffer): mixed
    {
        $sources = $prefetchBuffer->getObjectsByArguments($args);
        $toPassPrefetchArgs = QueryField::paramsToArguments($this->fieldName, $this->parameters, null, $args, $context, $info, $this->resolver);

        return ($this->resolver)($sources, ...$toPassPrefetchArgs);
    }

    /** @inheritDoc */
    public function toInputTypeParameters(): array
    {
        // Given these signatures:
        //   function name(#[Prefetch('prefetch1') $data1, string $arg2, #[Prefetch('prefetch2') $data2)
        //   function prefetch1(iterable $sources, int $arg1)
        //   function prefetch2(iterable $sources, int $arg3)
        // Then field `name` in GraphQL scheme should look like so: name(arg1: Int!, arg2: String!, arg3: Int!)
        // That's exactly what we're doing here - adding `arg1` and `arg3` from prefetch methods as input params
        return InputTypeUtils::toInputParameters($this->parameters);
    }
}
