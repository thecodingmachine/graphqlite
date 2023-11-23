<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Types;

use ArgumentCountError;
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
    private array $constructorInputFields = [];
    /** @var InputField[] */
    private array $inputFields = [];

    /** @param class-string<object> $className */
    public function __construct(
        private string $className,
        string $inputName,
        string|null $description,
        bool $isUpdate,
        FieldsBuilder $fieldsBuilder,
        private InputTypeValidatorInterface|null $inputTypeValidator = null,
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
    }

    /** @param array<string, mixed> $args */
    public function resolve(object|null $source, array $args, mixed $context, ResolveInfo $resolveInfo): object
    {
        // Sometimes developers may wish to pull the source from somewhere (like a model from a database)
        // instead of actually creating a new instance. So if given, we'll use that.
        $source ??= $this->createInstance($this->makeConstructorArgs($source, $args, $context, $resolveInfo));

        foreach ($this->inputFields as $inputField) {
            $name = $inputField->name;
            if (! array_key_exists($name, $args)) {
                continue;
            }

            $resolve = $inputField->getResolve();
            $resolve($source, $args, $context, $resolveInfo);
        }

        if ($this->inputTypeValidator && $this->inputTypeValidator->isEnabled()) {
            $this->inputTypeValidator->validate($source);
        }

        return $source;
    }

    public function decorate(callable $decorator): void
    {
        throw FailedResolvingInputType::createForDecorator($this->className);
    }

    /**
     * @param array<string, mixed> $args
     *
     * @return array<string, mixed>
     */
    private function makeConstructorArgs(object|null $source, array $args, mixed $context, ResolveInfo $resolveInfo): array
    {
        $constructorArgs = [];
        foreach ($this->constructorInputFields as $constructorInputField) {
            $name = $constructorInputField->name;
            $resolve = $constructorInputField->getResolve();

            if (! array_key_exists($name, $args)) {
                continue;
            }

            // Although $source will most likely be either `null` or unused by the resolver, we'll still
            // pass it in there in case the developer does want to use a source somehow.
            $constructorArgs[$name] = $resolve($source, $args, $context, $resolveInfo);
        }

        return $constructorArgs;
    }

    /**
     * Creates an instance of the input class.
     *
     * @param array<string, mixed> $values
     */
    private function createInstance(array $values): object
    {
        $refClass = new ReflectionClass($this->className);

        try {
            // This is the same as named parameters syntax, meaning default values are automatically used
            // and any missing properties without default values will throw a fatal error.
            return $refClass->newInstance(...$values);
        } catch (ArgumentCountError $e) {
            throw FailedResolvingInputType::createForMissingConstructorParameter($e);
        }
    }
}
