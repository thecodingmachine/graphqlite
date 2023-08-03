<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use GraphQL\Deferred;
use GraphQL\Error\ClientAware;
use GraphQL\Executor\Promise\Adapter\SyncPromise;
use GraphQL\Executor\Promise\Adapter\SyncPromiseAdapter;
use GraphQL\Executor\Promise\Promise;
use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use TheCodingMachine\GraphQLite\Exceptions\GraphQLAggregateException;
use TheCodingMachine\GraphQLite\Middlewares\ResolverInterface;
use TheCodingMachine\GraphQLite\Parameters\MissingArgumentException;
use TheCodingMachine\GraphQLite\Parameters\ParameterInterface;
use TheCodingMachine\GraphQLite\Parameters\SourceParameter;

use function array_filter;
use function array_map;

/**
 * A GraphQL field that maps to a PHP method automatically.
 *
 * @internal
 *
 * @phpstan-import-type FieldResolver from FieldDefinition
 * @phpstan-import-type ArgumentListConfig from FieldDefinition
 * @phpstan-import-type ComplexityFn from FieldDefinition
 */
final class QueryField extends FieldDefinition
{
    /**
     * @param OutputType&Type $type
     * @param array<string, ParameterInterface> $arguments Indexed by argument name.
     * @param ResolverInterface $originalResolver A pointer to the resolver being called (but not wrapped by any field middleware)
     * @param callable $resolver The resolver actually called
     * @param array{resolve?: FieldResolver|null,args?: ArgumentListConfig|null,description?: string|null,deprecationReason?: string|null,astNode?: FieldDefinitionNode|null,complexity?: ComplexityFn|null} $additionalConfig
     */
    public function __construct(
        string $name,
        OutputType $type,
        array $arguments,
        ResolverInterface $originalResolver,
        callable $resolver,
        string|null $comment,
        string|null $deprecationReason,
        array $additionalConfig = [],
    )
    {
        $config = [
            'name' => $name,
            'type' => $type,
            'args' => InputTypeUtils::getInputTypeArgs($arguments),
        ];
        if ($comment) {
            $config['description'] = $comment;
        }
        if ($deprecationReason) {
            $config['deprecationReason'] = $deprecationReason;
        }

        $config['resolve'] = function (object|null $source, array $args, $context, ResolveInfo $info) use ($name, $arguments, $originalResolver, $resolver) {
            /*if ($resolve !== null) {
                $method = $resolve;
            } elseif ($targetMethodOnSource !== null) {
                $method = [$source, $targetMethodOnSource];
            } else {
                throw new InvalidArgumentException('The QueryField constructor should be passed either a resolve method or a target method on source object.');
            }*/
            $toPassArgs = self::paramsToArguments($name, $arguments, $source, $args, $context, $info, $resolver);

            $callResolver = function (...$args) use ($originalResolver, $source, $resolver) {
                $result = $resolver($source, ...$args);

                try {
                    $this->assertReturnType($result);
                } catch (TypeMismatchRuntimeException $e) {
                    $e->addInfo($this->name, $originalResolver->toString());

                    throw $e;
                }

                return $result;
            };

            $deferred = (bool) array_filter($toPassArgs, static fn (mixed $value) => $value instanceof SyncPromise);

            // GraphQL allows deferring resolving the field's value using promises, i.e. they call the resolve
            // function ahead of time for all of the fields (allowing us to gather all calls and do something
            // in batch, like prefetch) and then resolve the promises as needed. To support that for prefetch,
            // we're checking if any of the resolved parameters returned a promise. If they did, we know
            // that the value should also be resolved using a promise, so we're wrapping it in one.
            return $deferred ? new Deferred(static function () use ($toPassArgs, $callResolver) {
                $syncPromiseAdapter = new SyncPromiseAdapter();

                // Wait for every deferred parameter.
                $toPassArgs = array_map(
                    static fn (mixed $value) => $value instanceof SyncPromise ? $syncPromiseAdapter->wait(new Promise($value, $syncPromiseAdapter)) : $value,
                    $toPassArgs,
                );

                return $callResolver(...$toPassArgs);
            }) : $callResolver(...$toPassArgs);
        };

        $config += $additionalConfig;

        parent::__construct($config);
    }

    /**
     * This method checks the returned value of the resolver to be sure it matches the documented return type.
     * We are sure the returned value is of the correct type... except if the return type is type-hinted as an array.
     * In this case, PHP does nothing for us and we should check the user returned what he documented.
     */
    private function assertReturnType(mixed $result): void
    {
        $type = $this->removeNonNull($this->getType());
        if (! $type instanceof ListOfType) {
            return;
        }

        ResolveUtils::assertInnerReturnType($result, $type);
    }

    private function removeNonNull(Type $type): Type
    {
        if ($type instanceof NonNull) {
            return $type->getWrappedType();
        }

        return $type;
    }

    /**
     * @param mixed $value A value that will always be returned by this field.
     *
     * @return QueryField
     */
    public static function alwaysReturn(QueryFieldDescriptor $fieldDescriptor, mixed $value): self
    {
        $callable = static function () use ($value) {
            return $value;
        };

        $fieldDescriptor = $fieldDescriptor->withResolver($callable);

        return self::fromDescriptor($fieldDescriptor);
    }

    private static function fromDescriptor(QueryFieldDescriptor $fieldDescriptor): self
    {
        return new self(
            $fieldDescriptor->getName(),
            $fieldDescriptor->getType(),
            $fieldDescriptor->getParameters(),
            $fieldDescriptor->getOriginalResolver(),
            $fieldDescriptor->getResolver(),
            $fieldDescriptor->getComment(),
            $fieldDescriptor->getDeprecationReason(),
        );
    }

    public static function fromFieldDescriptor(QueryFieldDescriptor $fieldDescriptor): self
    {
        $arguments = $fieldDescriptor->getParameters();
        if ($fieldDescriptor->isInjectSource() === true) {
            $arguments = ['__graphqlite_source' => new SourceParameter()] + $arguments;
        }
        $fieldDescriptor = $fieldDescriptor->withParameters($arguments);

        return self::fromDescriptor($fieldDescriptor);
    }

    /**
     * Casts parameters array into an array of arguments ready to be passed to the resolver.
     *
     * @param ParameterInterface[] $parameters
     * @param array<string, mixed> $args
     *
     * @return array<int, mixed>
     */
    public static function paramsToArguments(string $name, array $parameters, object|null $source, array $args, mixed $context, ResolveInfo $info, callable $resolve): array
    {
        $toPassArgs = [];
        $exceptions = [];
        foreach ($parameters as $parameter) {
            try {
                $toPassArgs[] = $parameter->resolve($source, $args, $context, $info);
            } catch (MissingArgumentException $e) {
                throw MissingArgumentException::wrapWithFieldContext($e, $name, $resolve);
            } catch (ClientAware $e) {
                $exceptions[] = $e;
            }
        }
        GraphQLAggregateException::throwExceptions($exceptions);

        return $toPassArgs;
    }
}
