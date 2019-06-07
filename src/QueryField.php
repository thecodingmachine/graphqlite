<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use GraphQL\Deferred;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use InvalidArgumentException;
use TheCodingMachine\GraphQLite\Middlewares\MissingAuthorizationException;
use TheCodingMachine\GraphQLite\Parameters\MissingArgumentException;
use TheCodingMachine\GraphQLite\Parameters\ParameterInterface;
use TheCodingMachine\GraphQLite\Parameters\PrefetchDataParameter;
use TheCodingMachine\GraphQLite\Parameters\SourceParameter;
use Webmozart\Assert\Assert;
use function array_map;
use function array_unshift;
use function array_values;

/**
 * A GraphQL field that maps to a PHP method automatically.
 *
 * @internal
 */
class QueryField extends FieldDefinition
{
    /**
     * @param OutputType&Type                 $type
     * @param array<string, ParameterInterface> $arguments            Indexed by argument name.
     * @param (callable&array<int,mixed>)|null  $resolve              The method to execute
     * @param string|null                       $targetMethodOnSource The name of the method to execute on the source object. Mutually exclusive with $resolve parameter.
     * @param array<string, ParameterInterface> $prefetchArgs         Indexed by argument name.
     * @param array<string, mixed>              $additionalConfig
     */
    public function __construct(string $name, OutputType $type, array $arguments, ?callable $resolve, ?string $targetMethodOnSource, ?string $comment, ?string $prefetchMethodName, array $prefetchArgs, array $additionalConfig = [])
    {
        $config = [
            'name' => $name,
            'type' => $type,
            'args' => InputTypeUtils::getInputTypeArgs($prefetchArgs + $arguments),
        ];
        if ($comment) {
            $config['description'] = $comment;
        }

        $resolveFn = function ($source, array $args, $context, ResolveInfo $info) use ($resolve, $targetMethodOnSource, $arguments) {
            if ($resolve !== null) {
                $method = $resolve;
            } elseif ($targetMethodOnSource !== null) {
                $method = [$source, $targetMethodOnSource];
                Assert::isCallable($method);
            } else {
                throw new InvalidArgumentException('The QueryField constructor should be passed either a resolve method or a target method on source object.');
            }

            $toPassArgs = $this->paramsToArguments($arguments, $source, $args, $context, $info, $method);

            return $method(...$toPassArgs);
        };

        if ($prefetchMethodName === null) {
            $config['resolve'] = $resolveFn;
        } else {
            $prefetchBuffer = new PrefetchBuffer();

            $config['resolve'] = function ($source, array $args, $context, ResolveInfo $info) use ($prefetchBuffer, $arguments, $prefetchArgs, $prefetchMethodName, $resolve, $resolveFn) {
                $prefetchBuffer->register($source, $args);

                return new Deferred(function () use ($prefetchBuffer, $source, $args, $context, $info, $prefetchArgs, $prefetchMethodName, $arguments, $resolveFn, $resolve) {
                    if (! $prefetchBuffer->hasResult($args)) {
                        if ($resolve) {
                            $prefetchCallable = [$resolve[0], $prefetchMethodName];
                        } else {
                            $prefetchCallable = [$source, $prefetchMethodName];
                        }

                        $sources = $prefetchBuffer->getObjectsByArguments($args);

                        $toPassPrefetchArgs = $this->paramsToArguments($prefetchArgs, $source, $args, $context, $info, $prefetchCallable);

                        array_unshift($toPassPrefetchArgs, $sources);
                        Assert::isCallable($prefetchCallable);
                        $prefetchResult = $prefetchCallable(...$toPassPrefetchArgs);
                        $prefetchBuffer->storeResult($prefetchResult, $args);
                    } else {
                        $prefetchResult = $prefetchBuffer->getResult($args);
                    }

                    foreach ($arguments as $argument) {
                        if (! ($argument instanceof PrefetchDataParameter)) {
                            continue;
                        }

                        $argument->setPrefetchedData($prefetchResult);
                    }

                    return $resolveFn($source, $args, $context, $info);
                });
            };
        }

        $config += $additionalConfig;
        parent::__construct($config);
    }

    /**
     * @param mixed                             $value     A value that will always be returned by this field.
     *
     * @return QueryField
     */
    public static function alwaysReturn(QueryFieldDescriptor $fieldDescriptor, $value): self
    {
        $callable = static function () use ($value) {
            return $value;
        };

        $fieldDescriptor->setCallable($callable);

        return self::fromDescriptor($fieldDescriptor);
    }

    /**
     * @param bool $isLogged True if the user is logged (and the error is a 403), false if the error is unlogged (the error is a 401)
     *
     * @return QueryField
     */
    public static function unauthorizedError(QueryFieldDescriptor $fieldDescriptor, bool $isLogged): self
    {
        $callable = static function () use ($isLogged): void {
            if (! $isLogged) {
                throw MissingAuthorizationException::forbidden();
            }

            throw MissingAuthorizationException::unauthorized();
        };

        $fieldDescriptor->setCallable($callable);

        return self::fromDescriptor($fieldDescriptor);
    }

    private static function fromDescriptor(QueryFieldDescriptor $fieldDescriptor): self
    {
        return new self(
            $fieldDescriptor->getName(),
            $fieldDescriptor->getType(),
            $fieldDescriptor->getParameters(),
            $fieldDescriptor->getCallable(),
            $fieldDescriptor->getTargetMethodOnSource(),
            $fieldDescriptor->getComment(),
            $fieldDescriptor->getPrefetchMethodName(),
            $fieldDescriptor->getPrefetchParameters()
        );
    }

    public static function selfField(QueryFieldDescriptor $fieldDescriptor): self
    {
        if ($fieldDescriptor->getPrefetchMethodName() !== null) {
            $arguments = $fieldDescriptor->getParameters();
            array_unshift($arguments, new PrefetchDataParameter());
            $fieldDescriptor->setParameters($arguments);
        }

        return self::fromDescriptor($fieldDescriptor);
    }

    public static function externalField(QueryFieldDescriptor $fieldDescriptor): self
    {
        $arguments = $fieldDescriptor->getParameters();
        if ($fieldDescriptor->getPrefetchMethodName() !== null) {
            array_unshift($arguments, new PrefetchDataParameter());
        }
        if ($fieldDescriptor->isInjectSource() === true) {
            array_unshift($arguments, new SourceParameter());
        }
        $fieldDescriptor->setParameters($arguments);

        return self::fromDescriptor($fieldDescriptor);
    }

    /**
     * Casts parameters array into an array of arguments ready to be passed to the resolver.
     *
     * @param ParameterInterface[] $parameters
     * @param array<string, mixed> $args
     * @param mixed $context
     *
     * @return array<int, mixed>
     */
    private function paramsToArguments(array $parameters, ?object $source, array $args, $context, ResolveInfo $info, callable $resolve): array
    {
        return array_values(array_map(function (ParameterInterface $parameter) use ($source, $args, $context, $info, $resolve) {
            try {
                return $parameter->resolve($source, $args, $context, $info);
            } catch (MissingArgumentException $e) {
                throw MissingArgumentException::wrapWithFieldContext($e, $this->name, $resolve);
            }
        }, $parameters));
    }
}
