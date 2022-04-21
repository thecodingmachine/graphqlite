<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use GraphQL\Deferred;
use GraphQL\Error\ClientAware;
use GraphQL\Type\Definition\InputObjectField;
use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use TheCodingMachine\GraphQLite\Context\ContextInterface;
use TheCodingMachine\GraphQLite\Exceptions\GraphQLAggregateException;
use TheCodingMachine\GraphQLite\Middlewares\MissingAuthorizationException;
use TheCodingMachine\GraphQLite\Middlewares\ResolverInterface;
use TheCodingMachine\GraphQLite\Middlewares\SourceResolverInterface;
use TheCodingMachine\GraphQLite\Parameters\MissingArgumentException;
use TheCodingMachine\GraphQLite\Parameters\ParameterInterface;
use TheCodingMachine\GraphQLite\Parameters\PrefetchDataParameter;
use TheCodingMachine\GraphQLite\Parameters\SourceParameter;
use Webmozart\Assert\Assert;

use function array_unshift;
use function get_class;
use function is_object;

/**
 * A GraphQL input field that maps to a PHP method automatically.
 *
 * @internal
 */
class InputField extends InputObjectField
{
    /** @var Callable */
    private $resolve;
    /**
     * @param InputType&Type $type
     * @param array<string, ParameterInterface> $arguments Indexed by argument name.
     * @param ResolverInterface $originalResolver A pointer to the resolver being called (but not wrapped by any field middleware)
     * @param callable $resolver The resolver actually called
     * @param mixed|null $defaultValue the default value set for this field
     * @param array<string, mixed> $additionalConfig
     */
    public function __construct(string $name, InputType $type, array $arguments, ResolverInterface $originalResolver, callable $resolver, ?string $comment, ?bool $isUpdate,?bool $hasDefaultValue, $defaultValue, array $additionalConfig = [])
    {
        $config = [
            'name' => $name,
            'type' => $type,
//            'args' => InputTypeUtils::getInputTypeArgs($prefetchArgs + $arguments),
        ];
        if ($comment) {
            $config['description'] = $comment;
        }
        if ($hasDefaultValue && !$isUpdate) {
            $config['defaultValue'] = $defaultValue;
        }

            $this->resolve = function ($source, array $args, $context, ResolveInfo $info) use ($arguments, $originalResolver, $resolver) {
                if ($originalResolver instanceof SourceResolverInterface) {
                    $originalResolver->setObject($source);
                }

                $toPassArgs = $this->paramsToArguments($arguments, $source, $args, $context, $info, $resolver);

                $result = $resolver(...$toPassArgs);

                try {
                    $this->assertInputType($result);
                } catch (TypeMismatchRuntimeException $e) {
//                    $class = $originalResolver->getObject();
//                    if (is_object($class)) {
//                        $class = get_class($class);
//                    }

                    $e->addInfo($this->name, $originalResolver->toString());
                    throw $e;
                }

                return $result;
            };

        $config += $additionalConfig;
        parent::__construct($config);
    }

    /**
     * @return Callable
     */
    public function getResolve()
    {
        return $this->resolve;
    }

    /**
     *
     * @param mixed $result
     */
    private function assertInputType($input): void
    {
        $type = $this->removeNonNull($this->getType());
        if (! $type instanceof ListOfType) {
            return;
        }

        ResolveUtils::assertInnerInputType($input, $type);
    }

    private function removeNonNull(Type $type): Type
    {
        if ($type instanceof NonNull) {
            return $type->getWrappedType();
        }

        return $type;
    }

//    /**
//     * @param mixed $value A value that will always be returned by this field.
//     *
//     * @return InputField
//     */
//    public static function alwaysReturn(InputFieldDescriptor $fieldDescriptor, $value): self
//    {
//        $callable = static function () use ($value) {
//            return $value;
//        };
//
//        $fieldDescriptor->setResolver($callable);
//
//        return self::fromDescriptor($fieldDescriptor);
//    }

//    /**
//     * @param bool $isNotLogged False if the user is logged (and the error is a 403), true if the error is unlogged (the error is a 401)
//     *
//     * @return InputField
//     */
//    public static function unauthorizedError(InputFieldDescriptor $fieldDescriptor, bool $isNotLogged): self
//    {
//        $callable = static function () use ($isNotLogged): void {
//            if ($isNotLogged) {
//                throw MissingAuthorizationException::unauthorized();
//            }
//            throw MissingAuthorizationException::forbidden();
//        };
//
//        $fieldDescriptor->setResolver($callable);
//
//        return self::fromDescriptor($fieldDescriptor);
//    }

    private static function fromDescriptor(InputFieldDescriptor $fieldDescriptor): self
    {
        return new self(
            $fieldDescriptor->getName(),
            $fieldDescriptor->getType(),
            $fieldDescriptor->getParameters(),
            $fieldDescriptor->getOriginalResolver(),
            $fieldDescriptor->getResolver(),
            $fieldDescriptor->getComment(),
            $fieldDescriptor->isUpdate(),
            $fieldDescriptor->hasDefaultValue(),
            $fieldDescriptor->getDefaultValue(),
        );
    }

    public static function fromFieldDescriptor(InputFieldDescriptor $fieldDescriptor): self
    {
        $arguments = $fieldDescriptor->getParameters();
//        if ($fieldDescriptor->getPrefetchMethodName() !== null) {
//            $arguments = ['__graphqlite_prefectData' => new PrefetchDataParameter()] + $arguments;
//        }
        if ($fieldDescriptor->isInjectSource() === true) {
            $arguments = ['__graphqlite_source' => new SourceParameter()] + $arguments;
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
        $toPassArgs = [];
        $exceptions = [];
        foreach ($parameters as $parameter) {
            try {
                $toPassArgs[] = $parameter->resolve($source, $args, $context, $info);
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
