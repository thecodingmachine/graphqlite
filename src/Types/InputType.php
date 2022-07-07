<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Types;

use GraphQL\Type\Definition\ResolveInfo;
use ReflectionClass;
use TheCodingMachine\GraphQLite\FailedResolvingInputType;
use TheCodingMachine\GraphQLite\FieldsBuilder;
use TheCodingMachine\GraphQLite\InputField;

use function array_key_exists;

/**
 * A class that maps input described by class to the GraphQL input object.
 */
class InputType extends MutableInputObjectType implements ResolvableMutableInputInterface
{
    /** @var InputField[] */
    private $constructorInputFields = [];
    /** @var InputField[] */
    private $inputFields = [];

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


        $fields = function () use ($isUpdate, $inputName, $className, $fieldsBuilder) {
            $inputFields = $fieldsBuilder->getInputFields($className, $inputName, $isUpdate);

            $fieldConfigs = [];
            foreach ($inputFields as $field) {
                if ($field->forConstructorHydration()) {
                    $this->constructorInputFields[] = $field;
                } else {
                    $this->inputFields[] = $field;
                }

                $fieldConfigs[] = $field->config;
            }
            return $fieldConfigs;
        };

        $fields = $fields->bindTo($this);

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
        $constructorArgs = [];
        foreach ($this->constructorInputFields as $constructorInputField) {
            $name = $constructorInputField->name;
            $resolve = $constructorInputField->getResolve();

            if (!array_key_exists($name, $args)) {
                continue;
            }

            $constructorArgs[$name] = $resolve(null, $args, $context, $resolveInfo);
        }

        $instance = $this->createInstance($constructorArgs);

        foreach ($this->inputFields as $inputField) {
            $name = $inputField->name;
            if (!array_key_exists($name, $args)) {
                continue;
            }

            $resolve = $inputField->getResolve();
            $resolve($instance, $args, $context, $resolveInfo);
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
}
