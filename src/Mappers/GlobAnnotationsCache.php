<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Mappers;

use TheCodingMachine\GraphQLite\Annotations\Input;
use TheCodingMachine\GraphQLite\Utils\Cloneable;

/**
 * An object containing a description of ALL annotations relevant to GlobTypeMapper for a given class.
 *
 * @internal
 */
final class GlobAnnotationsCache
{
    use Cloneable;

    /**
     * @param class-string<object>|null $typeClassName
     * @param array<string, array{0: string, 1:class-string<object>|null, 2:bool, 3:class-string<object>}> $factories An array mapping a factory method name to an input name / class name / default flag / declaring class
     * @param array<string, array{0: string, 1:class-string<object>}> $decorators An array mapping a decorator method name to an input name / declaring class
     * @param array<string, array{0: class-string<object>, 1: bool, 2: string|null, 3: bool}> $inputs An array mapping an input type name to an input name / declaring class
     */
    public function __construct(
        private readonly string|null $typeClassName = null,
        private readonly string|null $typeName = null,
        private readonly bool $default = false,
        private readonly array $factories = [],
        private readonly array $decorators = [],
        private readonly array $inputs = [],
    ) {
    }

    /** @param class-string<object> $className */
    public function withType(string $className, string $typeName, bool $isDefault): self
    {
        return $this->with(
            typeClassName: $className,
            typeName: $typeName,
            default: $isDefault,
        );
    }

    /** @return class-string<object>|null */
    public function getTypeClassName(): string|null
    {
        return $this->typeClassName;
    }

    public function getTypeName(): string|null
    {
        return $this->typeName;
    }

    public function isDefault(): bool
    {
        return $this->default;
    }

    /**
     * @param class-string<object>|null $className
     * @param class-string<object> $declaringClass
     */
    public function registerFactory(string $methodName, string $inputName, string|null $className, bool $isDefault, string $declaringClass): self
    {
        return $this->with(
            factories: [
                ...$this->factories,
                $methodName => [$inputName, $className, $isDefault, $declaringClass],
            ],
        );
    }

    /** @param class-string<object> $declaringClass */
    public function registerDecorator(string $methodName, string $inputName, string $declaringClass): self
    {
        return $this->with(
            decorators: [
                ...$this->decorators,
                $methodName => [$inputName, $declaringClass],
            ],
        );
    }

    /** @return array<string, array{0: string, 1:class-string<object>|null, 2:bool, 3:class-string<object>}> */
    public function getFactories(): array
    {
        return $this->factories;
    }

    /** @return array<string, array{0: string, 1:class-string<object>}> */
    public function getDecorators(): array
    {
        return $this->decorators;
    }

    /**
     * Register a new input.
     *
     * @param class-string<object> $className
     */
    public function registerInput(string $name, string $className, Input $input): self
    {
        return $this->with(
            inputs: [
                ...$this->inputs,
                $name => [$className, $input->isDefault(), $input->getDescription(), $input->isUpdate()],
            ],
        );
    }

    /**
     * Returns registered inputs.
     *
     * @return array<string, array{0: class-string<object>, 1: bool, 2: string|null, 3: bool}>
     */
    public function getInputs(): array
    {
        return $this->inputs;
    }
}
