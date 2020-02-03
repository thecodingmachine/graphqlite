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
    /** @var class-string<object>|null */
    private $typeClassName;

    /** @var string|null */
    private $typeName;

    /** @var bool */
    private $default;

    /** @var array<string, array{0: string, 1:class-string<object>|null, 2:bool, 3:class-string<object>}> An array mapping a factory method name to an input name / class name / default flag / declaring class */
    private $factories = [];

    /** @var array<string, array{0: string, 1:class-string<object>}> An array mapping a decorator method name to an input name / declaring class */
    private $decorators = [];

    /**
     * @param class-string<object> $className
     */
    public function setType(string $className, string $typeName, bool $isDefault): void
    {
        $this->typeClassName = $className;
        $this->typeName = $typeName;
        $this->default = $isDefault;
    }

    /**
     * @return class-string<object>|null
     */
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

    /**
     * @param class-string<object>|null $className
     * @param class-string<object> $declaringClass
     */
    public function registerFactory(string $methodName, string $inputName, ?string $className, bool $isDefault, string $declaringClass): void
    {
        $this->factories[$methodName] = [$inputName, $className, $isDefault, $declaringClass];
    }

    /**
     * @param class-string<object> $declaringClass
     */
    public function registerDecorator(string $methodName, string $inputName, string $declaringClass): void
    {
        $this->decorators[$methodName] = [$inputName, $declaringClass];
    }

    /**
     * @return array<string, array{0: string, 1:class-string<object>|null, 2:bool, 3:class-string<object>}>
     */
    public function getFactories(): array
    {
        return $this->factories;
    }

    /**
     * @return array<string, array{0: string, 1:class-string<object>}>
     */
    public function getDecorators(): array
    {
        return $this->decorators;
    }
}
