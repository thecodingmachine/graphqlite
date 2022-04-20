<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Types;

use GraphQL\Error\ClientAware;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\ResolveInfo;
use ReflectionClass;
use TheCodingMachine\GraphQLite\Exceptions\GraphQLAggregateException;
use TheCodingMachine\GraphQLite\FailedResolvingInputType;
use TheCodingMachine\GraphQLite\FieldsBuilder;
use TheCodingMachine\GraphQLite\Parameters\InputTypeMethod;
use TheCodingMachine\GraphQLite\Parameters\InputTypeProperty;
use TheCodingMachine\GraphQLite\Parameters\MissingArgumentException;
use TheCodingMachine\GraphQLite\Parameters\ParameterInterface;
use TheCodingMachine\GraphQLite\Utils\PropertyAccessor;

use function array_diff_key;
use function array_flip;
use function array_key_exists;

/**
 * A class that maps input described by class to the GraphQL input object.
 */
class InputType extends MutableInputObjectType implements ResolvableMutableInputInterface
{
    /** @var InputTypeProperty[] */
    private array $fields;

    /** @var class-string<object> */
    private $className;

    private ?InputTypeValidatorInterface $inputTypeValidator;

    /**
     * @param class-string<object> $className
     */
    public function __construct(
        string $className,
        string $inputName,
        ?string $description,
        bool $isUpdate,
        FieldsBuilder $fieldsBuilder,
        ?InputTypeValidatorInterface $inputTypeValidator = null
    ) {
        $reflection = new ReflectionClass($className);
        if (! $reflection->isInstantiable()) {
            throw FailedResolvingInputType::createForNotInstantiableClass($className);
        }

        $this->fields = $fieldsBuilder->getInputFields($className, $inputName, $isUpdate);

        $fields = function () use ($isUpdate) {
            $fields = [];
            foreach ($this->fields as $name => $field) {
                $type = $field->getType();

                if ($isUpdate && $type instanceof NonNull) {
                    $type = $type->getWrappedType();
                }

                $fields[$name] = [
                    'type' => $type,
                    'description' => $field->getDescription(),
                ];

                if (! $field->hasDefaultValue() || $isUpdate) {
                    continue;
                }

                $fields[$name]['defaultValue'] = $field->getDefaultValue();
            }

            return $fields;
        };

        $config = [
            'name' => $inputName,
            'description' => $description,
            'fields' => $fields,
        ];

        parent::__construct($config);
        $this->className = $className;
        $this->inputTypeValidator = $inputTypeValidator;
    }

    /**
     * @param array<string, mixed> $args
     * @param mixed                $context
     */
    public function resolve(?object $source, array $args, $context, ResolveInfo $resolveInfo): object
    {
        $mappedValues = [];
        $mappedMethodValues = [];
        foreach ($this->fields as $field) {
            $name = $field->getName();
            if (! array_key_exists($name, $args)) {
                continue;
            }
            if ($field instanceof InputTypeMethod) {
                $args = $this->paramsToArguments($field->getParameters(), $source, $args, $context, $resolveInfo, [$field, "resolve"]);
                $mappedMethodValues[$field->getMethodName()] = $args;
                $mappedValues[$name] = $args[$name];
            } else {
                $mappedValues[$field->getPropertyName()] = $field->resolve($source, $args, $context, $resolveInfo);
            }
        }

        $instance = $this->createInstance($mappedValues);
        $values = array_diff_key($mappedValues, array_flip($this->getClassConstructParameterNames()));

        foreach ($values as $property => $value) {
            PropertyAccessor::setValue($instance, $property, $value);
        }
        foreach ($mappedMethodValues as  $methodName => $args) {
            $instance->{$methodName}(...$args);
        }

        if ($this->inputTypeValidator && $this->inputTypeValidator->isEnabled()) {
            $this->inputTypeValidator->validate($instance);
        }

        return $instance;
    }

    public function decorate(callable $decorator): void
    {
        throw FailedResolvingInputType::createForDecorator($this->className);
    }

    /**
     * Creates an instance of the input class.
     *
     * @param array<string, mixed> $values
     */
    private function createInstance(array $values): object
    {
        $refClass = new ReflectionClass($this->className);
        $constructor = $refClass->getConstructor();
        $constructorParameters = $constructor ? $constructor->getParameters() : [];

        $parameters = [];
        foreach ($constructorParameters as $parameter) {
            $name = $parameter->getName();
            if (! array_key_exists($name, $values)) {
                if (! $parameter->isDefaultValueAvailable()) {
                    throw FailedResolvingInputType::createForMissingConstructorParameter($refClass->getName(), $name);
                }

                $values[$name] = $parameter->getDefaultValue();
            }

            $parameters[] = $values[$name];
        }

        return $refClass->newInstanceArgs($parameters);
    }

    /**
     * @return string[]
     */
    private function getClassConstructParameterNames(): array
    {
        $refClass = new ReflectionClass($this->className);
        $constructor = $refClass->getConstructor();

        if (! $constructor) {
            return [];
        }

        $names = [];
        foreach ($constructor->getParameters() as $parameter) {
            $names[] = $parameter->getName();
        }

        return $names;
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
        foreach ($parameters as $parameterName => $parameter) {
            try {
                $toPassArgs[$parameterName] = $parameter->resolve($source, $args, $context, $info);
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
