<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Mappers;

/**
 * An object containing a description of ALL annotations relevant to GlobTypeMapper for a given class.
 *
 * @internal
 */
final class GlobAnnotationsCache
{
    /** @var string|null */
    private $typeClassName;

    /** @var string|null */
    private $typeName;

    /** @var bool */
    private $default;

    /** @var array<string, array<int, string|bool>> An array mapping a factory method name to an input name / class name / default flag / declaring class */
    private $factories = [];

    /** @var array<string, array<int, string>> An array mapping a decorator method name to an input name / declaring class */
    private $decorators = [];

    public function setType(string $className, string $typeName, bool $isDefault): void
    {
        $this->typeClassName = $className;
        $this->typeName = $typeName;
        $this->default = $isDefault;
    }

    public function getTypeClassName(): ?string
    {
        return $this->typeClassName;
    }

    public function getTypeName(): ?string
    {
        return $this->typeName;
    }

    public function isDefault(): bool
    {
        return $this->default;
    }

    public function registerFactory(string $methodName, string $inputName, ?string $className, bool $isDefault, string $declaringClass): void
    {
        $this->factories[$methodName] = [$inputName, $className, $isDefault, $declaringClass];
    }

    public function registerDecorator(string $methodName, string $inputName, string $declaringClass): void
    {
        $this->decorators[$methodName] = [$inputName, $declaringClass];
    }

    /**
     * @return array<string, array<int, string|bool>>
     */
    public function getFactories(): array
    {
        return $this->factories;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function getDecorators(): array
    {
        return $this->decorators;
    }
}
