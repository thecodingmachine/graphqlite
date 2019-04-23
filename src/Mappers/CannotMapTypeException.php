<?php


namespace TheCodingMachine\GraphQLite\Mappers;


use GraphQL\Error\SyntaxError;
use GraphQL\Type\Definition\ObjectType;
use ReflectionClass;
use ReflectionMethod;
use function sprintf;
use TheCodingMachine\GraphQLite\Annotations\SourceField;

class CannotMapTypeException extends \Exception implements CannotMapTypeExceptionInterface
{
    public static function createForType(string $className): self
    {
        return new self('cannot map class "'.$className.'" to a known GraphQL type. Check your TypeMapper configuration.');
    }

    public static function createForInputType(string $className): self
    {
        return new self('cannot map class "'.$className.'" to a known GraphQL input type. Check your TypeMapper configuration.');
    }

    public static function createForName(string $name): self
    {
        return new self('cannot find GraphQL type "'.$name.'". Check your TypeMapper configuration.');
    }

    public static function createForParseError(SyntaxError $error): self
    {
        return new self($error->getMessage(), $error->getCode(), $error);
    }

    public static function wrapWithParamInfo(CannotMapTypeExceptionInterface $previous, \ReflectionParameter $parameter): self
    {
        $message = sprintf('For parameter $%s, in %s::%s, %s',
            $parameter->getName(),
            $parameter->getDeclaringClass()->getName(),
            $parameter->getDeclaringFunction()->getName(),
            $previous->getMessage());

        return new self($message, 0, $previous);
    }

    public static function wrapWithReturnInfo(CannotMapTypeExceptionInterface $previous, ReflectionMethod $method): self
    {
        $message = sprintf('For return type of %s::%s, %s',
            $method->getDeclaringClass()->getName(),
            $method->getName(),
            $previous->getMessage());

        return new self($message, 0, $previous);
    }

    public static function wrapWithSourceField(CannotMapTypeExceptionInterface $previous, ReflectionClass $class, SourceField $sourceField): self
    {
        $message = sprintf('For @SourceField "%s" declared in "%s", %s',
            $sourceField->getName(),
            $class->getName(),
            $previous->getMessage());

        return new self($message, 0, $previous);
    }

    public static function mustBeOutputType($subTypeName): self
    {
        return new self('type "'.$subTypeName.'" must be an output type.');
    }

    public static function createForExtendType(string $className, ObjectType $type): self
    {
        return new self('cannot extend GraphQL type "'.$type->name.'" mapped by class "'.$className.'". Check your TypeMapper configuration.');
    }

    public static function createForExtendName(string $name, ObjectType $type): self
    {
        return new self('cannot extend GraphQL type "'.$type->name.'" with type "'.$name.'". Check your TypeMapper configuration.');
    }
}
