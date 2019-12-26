<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Mappers;

use Exception;
use GraphQL\Error\Error;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\NamedType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use phpDocumentor\Reflection\Type as PhpDocumentorType;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Iterable_;
use phpDocumentor\Reflection\Types\Mixed_;
use ReflectionMethod;
use TheCodingMachine\GraphQLite\Annotations\ExtendType;
use function array_map;
use function assert;
use function implode;
use function sprintf;

class CannotMapTypeException extends Exception implements CannotMapTypeExceptionInterface
{
    use CannotMapTypeTrait;

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

    public static function createForMissingIteratorValue(string $className, self $e): self
    {
        $message = sprintf(
            '"%s" is iterable. Please provide a more specific type. For instance: %s|User[].',
            $className,
            $className
        );

        return new self($message, 0, $e);
    }

    /**
     * @param Type[] $unionTypes
     *
     * @return CannotMapTypeException
     */
    public static function createForBadTypeInUnion(array $unionTypes): self
    {
        $disallowedTypeNames = array_map(static function (Type $type) {
            return (string) $type;
        }, $unionTypes);

        return new self('in GraphQL, you can only use union types between objects. These types cannot be used in union types: ' . implode(', ', $disallowedTypeNames));
    }

    public static function createForBadTypeInUnionWithIterable(Type $type): self
    {
        return new self('the value must be iterable, but its computed GraphQL type (' . $type . ') is not a list.');
    }

    public static function mustBeOutputType(string $subTypeName): self
    {
        return new self('type "' . $subTypeName . '" must be an output type.');
    }

    public static function mustBeInputType(string $subTypeName): self
    {
        return new self('type "' . $subTypeName . '" must be an input type (if you declared an input type with the name "' . $subTypeName . '", make sure that there are no output type with the same name as this is forbidden by the GraphQL spec).');
    }

    /**
     * @param NamedType&(ObjectType|InterfaceType) $type
     *
     * @return CannotMapTypeException
     */
    public static function createForExtendType(string $className, NamedType $type): self
    {
        return new self('cannot extend GraphQL type "' . $type->name . '" mapped by class "' . $className . '". Check your TypeMapper configuration.');
    }

    /**
     * @param NamedType&(ObjectType|InterfaceType) $type
     *
     * @return CannotMapTypeException
     */
    public static function createForExtendName(string $name, NamedType $type): self
    {
        return new self('cannot extend GraphQL type "' . $type->name . '" with type "' . $name . '". Check your TypeMapper configuration.');
    }

    public static function createForDecorateName(string $name, InputObjectType $type): self
    {
        return new self('cannot decorate GraphQL input type "' . $type->name . '" with type "' . $name . '". Check your TypeMapper configuration.');
    }

    public static function extendTypeWithBadTargetedClass(string $className, ExtendType $extendType): self
    {
        return new self('For ' . self::extendTypeToString($extendType) . ' annotation declared in class "' . $className . '", the pointed at GraphQL type cannot be extended. You can only target types extending the MutableObjectType (like types created with the @Type annotation).');
    }

    /**
     * @param Array_|Iterable_|Mixed_ $type
     */
    public static function createForMissingPhpDoc(PhpDocumentorType $type, ReflectionMethod $refMethod, ?string $argumentName = null): self
    {
        $typeStr = '';
        if ($type instanceof Array_) {
            $typeStr = 'array';
        } elseif ($type instanceof Iterable_) {
            $typeStr = 'iterable';
        } elseif ($type instanceof Mixed_) {
            $typeStr = 'mixed';
        }
        assert($typeStr !== '');
        if ($argumentName === null) {
            if ($typeStr === 'mixed') {
                return new self('a type-hint is missing (or PHPDoc specifies a "mixed" type-hint). Please provide a better type-hint.');
            }

            return new self(sprintf('please provide an additional @return in the PHPDoc block to further specify the return type of %s. For instance: @return string[]', $typeStr));
        }

        if ($typeStr === 'mixed') {
            return new self(sprintf('a type-hint is missing (or PHPDoc specifies a "mixed" type-hint). Please provide a better type-hint. For instance: "string $%s".', $argumentName));
        }

        return new self(sprintf('please provide an additional @param in the PHPDoc block to further specify the type of the %s. For instance: @param string[] $%s.', $typeStr, $argumentName));
    }

    public static function createForDateTime(): self
    {
        return new self('type-hinting against DateTime is not allowed. Please use the DateTimeImmutable type instead.');
    }

    public static function createForNull(): self
    {
        return new self('type-hinting against null only in the PHPDoc is not allowed.');
    }

    public static function createForInputUnionType(PhpDocumentorType $type): self
    {
        return new self('parameter is type-hinted to "' . $type . '". Type-hinting a parameter to a union type is forbidden in GraphQL. Only return types can be union types.');
    }

    public static function createForPhpDocType(PhpDocumentorType $type): self
    {
        return new self("don't know how to handle type " . (string) $type);
    }
}
