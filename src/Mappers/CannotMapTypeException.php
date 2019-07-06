<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Mappers;

use Exception;
use GraphQL\Error\Error;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\NamedType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use TheCodingMachine\GraphQLite\Annotations\ExtendType;
use TheCodingMachine\GraphQLite\Annotations\SourceFieldInterface;
use Webmozart\Assert\Assert;
use function array_filter;
use function array_map;
use function implode;
use function sprintf;

class CannotMapTypeException extends Exception implements CannotMapTypeExceptionInterface
{
    public static function createForType(string $className): self
    {
        return new self('cannot map class "' . $className . '" to a known GraphQL type. Check your TypeMapper configuration.');
    }

    public static function createForInputType(string $className): self
    {
        return new self('cannot map class "' . $className . '" to a known GraphQL input type. Check your TypeMapper configuration.');
    }

    public static function createForName(string $name): self
    {
        return new self('cannot find GraphQL type "' . $name . '". Check your TypeMapper configuration.');
    }

    public static function createForParseError(Error $error): self
    {
        return new self($error->getMessage(), $error->getCode(), $error);
    }

    /**
     * @param Type[] $unionTypes
     *
     * @return CannotMapTypeException
     */
    public static function createForBadTypeInUnion(array $unionTypes): self
    {
        $disallowedTypes = array_filter($unionTypes, static function (Type $type) {
            return $type instanceof NamedType;
        });
        $disallowedTypeNames = array_map(static function (NamedType $type) {
            return $type->name;
        }, $disallowedTypes);

        return new self('In GraphQL, you can only use union types between objects. These types cannot be used in union types: ' . implode(', ', $disallowedTypeNames));
    }

    public static function wrapWithParamInfo(CannotMapTypeExceptionInterface $previous, ReflectionParameter $parameter): self
    {
        $declaringClass = $parameter->getDeclaringClass();
        Assert::notNull($declaringClass, 'Parameter passed must be a parameter of a method, not a parameter of a function.');

        $message = sprintf(
            'For parameter $%s, in %s::%s, %s',
            $parameter->getName(),
            $declaringClass->getName(),
            $parameter->getDeclaringFunction()->getName(),
            $previous->getMessage()
        );

        return new self($message, 0, $previous);
    }

    public static function wrapWithReturnInfo(CannotMapTypeExceptionInterface $previous, ReflectionMethod $method): self
    {
        $message = sprintf(
            'For return type of %s::%s, %s',
            $method->getDeclaringClass()->getName(),
            $method->getName(),
            $previous->getMessage()
        );

        return new self($message, 0, $previous);
    }

    public static function wrapWithSourceField(CannotMapTypeExceptionInterface $previous, ReflectionClass $class, SourceFieldInterface $sourceField): self
    {
        $message = sprintf(
            'For @SourceField "%s" declared in "%s", %s',
            $sourceField->getName(),
            $class->getName(),
            $previous->getMessage()
        );

        return new self($message, 0, $previous);
    }

    public static function mustBeOutputType(string $subTypeName): self
    {
        return new self('type "' . $subTypeName . '" must be an output type.');
    }

    public static function mustBeInputType(string $subTypeName): self
    {
        return new self('type "' . $subTypeName . '" must be an input type.');
    }

    public static function createForExtendType(string $className, ObjectType $type): self
    {
        return new self('cannot extend GraphQL type "' . $type->name . '" mapped by class "' . $className . '". Check your TypeMapper configuration.');
    }

    public static function createForExtendName(string $name, ObjectType $type): self
    {
        return new self('cannot extend GraphQL type "' . $type->name . '" with type "' . $name . '". Check your TypeMapper configuration.');
    }

    public static function createForDecorateName(string $name, InputObjectType $type): self
    {
        return new self('cannot decorate GraphQL input type "' . $type->name . '" with type "' . $name . '". Check your TypeMapper configuration.');
    }

    public static function extendTypeWithInvalidName(ExtendType $extendType, string $className): self
    {
        return new self('For @ExtendType(name="'.$extendType->getName().'") annotation declared in class "'.$className.'", the "'.$extendType->getName().'" GraphQL type cannot be extended. You can only target types created with the @Type annotation.');
    }
}
