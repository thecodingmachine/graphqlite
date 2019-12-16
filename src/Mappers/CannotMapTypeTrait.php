<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Mappers;

use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use TheCodingMachine\GraphQLite\Annotations\ExtendType;
use TheCodingMachine\GraphQLite\Annotations\SourceFieldInterface;
use Webmozart\Assert\Assert;
use function sprintf;

trait CannotMapTypeTrait
{
    /** @var bool */
    private $locationInfoAdded = false;

    public function addParamInfo(ReflectionParameter $parameter): void
    {
        $declaringClass = $parameter->getDeclaringClass();
        Assert::notNull($declaringClass, 'Parameter passed must be a parameter of a method, not a parameter of a function.');

        if ($this->locationInfoAdded !== false) {
            return;
        }

        $this->locationInfoAdded = true;
        $this->message = sprintf(
            'For parameter $%s, in %s::%s, %s',
            $parameter->getName(),
            $declaringClass->getName(),
            $parameter->getDeclaringFunction()->getName(),
            $this->message
        );
    }

    public function addReturnInfo(ReflectionMethod $method): void
    {
        if ($this->locationInfoAdded !== false) {
            return;
        }

        $this->locationInfoAdded = true;
        $this->message = sprintf(
            'For return type of %s::%s, %s',
            $method->getDeclaringClass()->getName(),
            $method->getName(),
            $this->message
        );
    }

    /**
     * @param ReflectionClass<object> $class
     */
    public function addSourceFieldInfo(ReflectionClass $class, SourceFieldInterface $sourceField): void
    {
        if ($this->locationInfoAdded !== false) {
            return;
        }

        $this->locationInfoAdded = true;
        $this->message = sprintf(
            'For @SourceField "%s" declared in "%s", %s',
            $sourceField->getName(),
            $class->getName(),
            $this->message
        );
    }

    /**
     * @param ReflectionClass<object> $class
     */
    public function addExtendTypeInfo(ReflectionClass $class, ExtendType $extendType): void
    {
        if ($this->locationInfoAdded !== false) {
            return;
        }

        $this->locationInfoAdded = true;
        $this->message = 'For ' . self::extendTypeToString($extendType) . ' annotation declared in class "' . $class->getName() . '", ' . $this->message;
    }

    private static function extendTypeToString(ExtendType $extendType): string
    {
        $attribute = 'class="' . $extendType->getClass() . '"';
        if ($extendType->getName() !== null) {
            $attribute = 'name="' . $extendType->getName() . '"';
        }

        return '@ExtendType(' . $attribute . ')';
    }
}
