<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use GraphQL\Deferred;
use GraphQL\Error\ClientAware;
use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use TheCodingMachine\GraphQLite\Context\ContextInterface;
use TheCodingMachine\GraphQLite\Exceptions\GraphQLAggregateException;
use TheCodingMachine\GraphQLite\Middlewares\ResolverInterface;
use TheCodingMachine\GraphQLite\Middlewares\ServiceResolver;
use TheCodingMachine\GraphQLite\Parameters\MissingArgumentException;
use TheCodingMachine\GraphQLite\Parameters\ParameterInterface;
use TheCodingMachine\GraphQLite\Parameters\PrefetchDataParameter;
use TheCodingMachine\GraphQLite\Parameters\SourceParameter;

use function array_unshift;
use function assert;
use function is_callable;
use function is_object;

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
        string|null $prefetchMethodName,
        array $prefetchArgs,
        array $additionalConfig = []
    )
    {
        $config = [
            'name' => $name,
            'type' => $type,
            'args' => InputTypeUtils::getInputTypeArgs($prefetchArgs + $arguments),
        ];
        if ($comment) {
            $config['description'] = $comment;
        }
        if ($deprecationReason) {
            $config['deprecationReason'] = $deprecationReason;
        }

        $resolveFn = function (object|null $source, array $args, $context, ResolveInfo $info) use ($arguments, $originalResolver, $resolver) {
            /*if ($resolve !== null) {
                $method = $resolve;
            } elseif ($targetMethodOnSource !== null) {
                $method = [$source, $targetMethodOnSource];
            } else {
                throw new InvalidArgumentException('The QueryField constructor should be passed either a resolve method or a target method on source object.');
            }*/
            $toPassArgs = $this->paramsToArguments($arguments, $source, $args, $context, $info, $resolver);

            $result = $resolver($source, ...$toPassArgs);

            try {
                $this->assertReturnType($result);
            } catch (TypeMismatchRuntimeException $e) {
                $e->addInfo($this->name, $originalResolver->toString());

                throw $e;
            }

            return $result;
        };

        if ($prefetchMethodName === null) {
            $config['resolve'] = $resolveFn;
        } else {
            $config['resolve'] = function ($source, array $args, $context, ResolveInfo $info) use ($arguments, $prefetchArgs, $resolveFn, $originalResolver) {
                // The PrefetchBuffer must be tied to the current request execution. The only object we have for this is $context
                // $context MUST be a ContextInterface
                if (! $context instanceof ContextInterface) {
                    throw new GraphQLRuntimeException('When using "prefetch", you sure ensure that the GraphQL execution "context" (passed to the GraphQL::executeQuery method) is an instance of \TheCodingMachine\GraphQLite\Context\Context');
                }

                $context->getPrefetchBuffer($this)->register($source, $args);

                return new Deferred(function () use ($source, $args, $context, $info, $resolveFn) {
                    return $resolveFn($source, $args, $context, $info);
                });
            };
        }

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
            $fieldDescriptor->name,
            $fieldDescriptor->type,
            $fieldDescriptor->parameters,
            $fieldDescriptor->getOriginalResolver(),
            $fieldDescriptor->getResolver(),
            $fieldDescriptor->comment,
            $fieldDescriptor->deprecationReason,
            $fieldDescriptor->prefetchMethodName,
            $fieldDescriptor->prefetchParameters,
        );
    }

    public static function fromFieldDescriptor(QueryFieldDescriptor $fieldDescriptor): self
    {
        $arguments = $fieldDescriptor->parameters;
        if ($fieldDescriptor->prefetchMethodName !== null) {
            $arguments = [
                '__graphqlite_prefectData' => new PrefetchDataParameter(
                    originalResolver: $fieldDescriptor->getOriginalResolver(),
                    methodName: $fieldDescriptor->prefetchMethodName,
                    parameters: $fieldDescriptor->prefetchParameters,
                )
            ] + $arguments;
        }
        if ($fieldDescriptor->injectSource === true) {
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
    public function paramsToArguments(array $parameters, object|null $source, array $args, mixed $context, ResolveInfo $info, callable $resolve): array
    {
        $toPassArgs = [];
        $exceptions = [];
        foreach ($parameters as $parameter) {
            try {
                $toPassArgs[] = $parameter->resolve($source, $args, $context, $info, $this);
            } catch (MissingArgumentException $e) {
                throw MissingArgumentException::wrapWithFieldContext($e, $this->name, $resolve);
            } catch (ClientAware $e) {
                $exceptions[] = $e;
            }
        }
        GraphQLAggregateException::throwExceptions($exceptions);

        return $toPassArgs;
    }
}
