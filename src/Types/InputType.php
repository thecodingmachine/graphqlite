<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Types;

use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\ResolveInfo;
use ReflectionClass;
use TheCodingMachine\GraphQLite\FailedResolvingInputType;
use TheCodingMachine\GraphQLite\FieldsBuilder;
use TheCodingMachine\GraphQLite\Parameters\InputTypeProperty;
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
    private $fields;

    /** @var class-string<object> */
    private $className;

    /**
     * @param class-string<object> $className
     */
    public function __construct(string $className, string $inputName, ?string $description, bool $isUpdate, FieldsBuilder $fieldsBuilder)
    {
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
    }

    /**
     * @param array<string, mixed> $args
     * @param mixed                $context
     */
    public function resolve(?object $source, array $args, $context, ResolveInfo $resolveInfo): object
    {
        $mappedValues = [];
        foreach ($this->fields as $field) {
            $name = $field->getName();
            if (! array_key_exists($name, $args)) {
                continue;
            }

            $mappedValues[$field->getPropertyName()] = $field->resolve($source, $args, $context, $resolveInfo);
        }

        $instance = $this->createInstance($mappedValues);
        $values = array_diff_key($mappedValues, array_flip($this->getClassConstructParameterNames()));

        foreach ($values as $property => $value) {
            PropertyAccessor::setValue($instance, $property, $value);
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
}
