<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use GraphQL\Error\ClientAware;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Type\Definition\InputObjectField;
use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use TheCodingMachine\GraphQLite\Exceptions\GraphQLAggregateException;
use TheCodingMachine\GraphQLite\Middlewares\ResolverInterface;
use TheCodingMachine\GraphQLite\Middlewares\SourceResolverInterface;
use TheCodingMachine\GraphQLite\Parameters\MissingArgumentException;
use TheCodingMachine\GraphQLite\Parameters\ParameterInterface;
use TheCodingMachine\GraphQLite\Parameters\SourceParameter;
use Throwable;

/**
 * A GraphQL input field that maps to a PHP method automatically.
 *
 * @internal
 *
 * @phpstan-import-type InputObjectFieldConfig from InputObjectField
 * @phpstan-import-type ArgumentType from InputObjectField
 */
final class InputField extends InputObjectField
{
    /** @var callable */
    private $resolve;

    private bool $forConstructorHydration = false;

    /**
     * @param (Type&InputType) $type
     * @param array<string, ParameterInterface> $arguments Indexed by argument name.
     * @param mixed|null $defaultValue the default value set for this field
     * @param array{defaultValue?: mixed,description?: string|null,astNode?: InputValueDefinitionNode|null}|null $additionalConfig
     */
    public function __construct(string $name, InputType $type, array $arguments, ResolverInterface|null $originalResolver, callable|null $resolver, string|null $comment, bool $isUpdate, bool $hasDefaultValue, mixed $defaultValue, array|null $additionalConfig = null)
    {
        $config = [
            'name' => $name,
            'type' => $type,
            'description' => $comment,
        ];

        if (! (! $hasDefaultValue || $isUpdate)) {
            $config['defaultValue'] = $defaultValue;
        }

        if ($originalResolver !== null && $resolver !== null) {
            $this->resolve = function ($source, array $args, $context, ResolveInfo $info) use ($arguments, $originalResolver, $resolver) {
                if ($originalResolver instanceof SourceResolverInterface) {
                    $originalResolver->setObject($source);
                }
                $toPassArgs = $this->paramsToArguments($arguments, $source, $args, $context, $info, $resolver);
                $result = $resolver(...$toPassArgs);

                try {
                    $this->assertInputType($result);
                } catch (TypeMismatchRuntimeException $e) {
                    $e->addInfo($this->name, $originalResolver->toString());
                    throw $e;
                }

                return $result;
            };
        } else {
            $this->forConstructorHydration = true;
            $this->resolve = function ($source, array $args, $context, ResolveInfo $info) use ($arguments) {
                $result = $arguments[$this->name]->resolve($source, $args, $context, $info);
                $this->assertInputType($result);
                return $result;
            };
        }
        if ($additionalConfig !== null) {
            if (isset($additionalConfig['astNode'])) {
                $config['astNode'] = $additionalConfig['astNode'];
            }
            if (isset($additionalConfig['defaultValue'])) {
                $config['defaultValue'] = $additionalConfig['defaultValue'];
            }
            if (isset($additionalConfig['description'])) {
                $config['description'] = $additionalConfig['description'];
            }
        }

        parent::__construct($config);
    }

    public function getResolve(): callable
    {
        return $this->resolve;
    }

    private function assertInputType(mixed $input): void
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

    public function forConstructorHydration(): bool
    {
        return $this->forConstructorHydration;
    }

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
     *
     * @return array<int, mixed>
     */
    private function paramsToArguments(array $parameters, object|null $source, array $args, mixed $context, ResolveInfo $info, callable $resolve): array
    {
        $toPassArgs = [];
        /** @var (ClientAware&Throwable)[] $exceptions */
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
