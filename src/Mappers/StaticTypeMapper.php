<?php


namespace TheCodingMachine\GraphQLite\Mappers;
use function array_keys;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\Type;
use TheCodingMachine\GraphQLite\Mappers\Interfaces\InterfacesResolverInterface;
use TheCodingMachine\GraphQLite\Types\MutableObjectType;

/**
 * A simple implementation of the TypeMapperInterface that expects mapping to be passed in a setter.
 *
 * Note: no constructor argument as this results in a loop most of the time.
 */
final class StaticTypeMapper implements TypeMapperInterface
{
    /**
     * @var array<string,MutableObjectType>
     */
    private $types = [];

    /**
     * An array mapping a fully qualified class name to the matching TypeInterface
     *
     * @param array<string,MutableObjectType> $types
     */
    public function setTypes(array $types): void
    {
        $this->types = $types;
    }

    /**
     * @var array<string,InputObjectType>
     */
    private $inputTypes = [];

    /**
     * An array mapping a fully qualified class name to the matching InputTypeInterface
     *
     * @param array<string,InputObjectType> $inputTypes
     */
    public function setInputTypes(array $inputTypes): void
    {
        $this->inputTypes = $inputTypes;
    }

    /**
     * @var array<string,MutableObjectType|InputObjectType>
     */
    private $notMappedTypes = [];

    /**
     * An array containing ObjectType or InputObjectType instances that are not mapped by default to any class.
     * ObjectType not linked to any type by default will have to be accessed using the outputType attribute of the annotations.
     *
     * @param array<int,Type> $types
     */
    public function setNotMappedTypes(array $types): void
    {
        $this->notMappedTypes = array_reduce($types, function ($result, Type $type) {
            $result[$type->name] = $type;
            return $result;
        }, []);
    }

    /**
     * Returns true if this type mapper can map the $className FQCN to a GraphQL type.
     *
     * @param string $className
     * @return bool
     */
    public function canMapClassToType(string $className): bool
    {
        return isset($this->types[$className]);
    }

    /**
     * Maps a PHP fully qualified class name to a GraphQL type.
     *
     * @param string $className
     * @param OutputType|null $subType
     * @param RecursiveTypeMapperInterface $recursiveTypeMapper
     * @return MutableObjectType
     * @throws CannotMapTypeExceptionInterface
     */
    public function mapClassToType(string $className, ?OutputType $subType, RecursiveTypeMapperInterface $recursiveTypeMapper): MutableObjectType
    {
        // TODO: add support for $subType
        if ($subType !== null) {
            throw CannotMapTypeException::createForType($subType);
        }

        if (isset($this->types[$className])) {
            return $this->types[$className];
        }
        throw CannotMapTypeException::createForType($className);
    }

    /**
     * Returns the list of classes that have matching input GraphQL types.
     *
     * @return string[]
     */
    public function getSupportedClasses(): array
    {
        return array_keys($this->types);
    }

    /**
     * Returns true if this type mapper can map the $className FQCN to a GraphQL input type.
     *
     * @param string $className
     * @return bool
     */
    public function canMapClassToInputType(string $className): bool
    {
        return isset($this->inputTypes[$className]);
    }

    /**
     * Maps a PHP fully qualified class name to a GraphQL input type.
     *
     * @param string $className
     * @param RecursiveTypeMapperInterface $recursiveTypeMapper
     * @return InputObjectType
     * @throws CannotMapTypeExceptionInterface
     */
    public function mapClassToInputType(string $className, RecursiveTypeMapperInterface $recursiveTypeMapper): InputObjectType
    {
        if (isset($this->inputTypes[$className])) {
            return $this->inputTypes[$className];
        }
        throw CannotMapTypeException::createForInputType($className);
    }

    /**
     * Returns a GraphQL type by name (can be either an input or output type)
     *
     * @param string $typeName The name of the GraphQL type
     * @param RecursiveTypeMapperInterface $recursiveTypeMapper
     * @return Type&(InputType|OutputType)
     * @throws CannotMapTypeExceptionInterface
     */
    public function mapNameToType(string $typeName, RecursiveTypeMapperInterface $recursiveTypeMapper): Type
    {
        if (isset($this->notMappedTypes[$typeName])) {
            return $this->notMappedTypes[$typeName];
        }
        foreach ($this->types as $type) {
            if ($type->name === $typeName) {
                return $type;
            }
        }
        foreach ($this->inputTypes as $inputType) {
            if ($inputType->name === $typeName) {
                return $inputType;
            }
        }
        throw CannotMapTypeException::createForName($typeName);
    }

    /**
     * Returns true if this type mapper can map the $typeName GraphQL name to a GraphQL type.
     *
     * @param string $typeName The name of the GraphQL type
     * @return bool
     */
    public function canMapNameToType(string $typeName): bool
    {
        foreach ($this->types as $type) {
            if ($type->name === $typeName) {
                return true;
            }
        }
        foreach ($this->inputTypes as $inputType) {
            if ($inputType->name === $typeName) {
                return true;
            }
        }
        return isset($this->notMappedTypes[$typeName]);
    }

    /**
     * Returns true if this type mapper can extend an existing type for the $className FQCN
     *
     * @param string $className
     * @param MutableObjectType $type
     * @param RecursiveTypeMapperInterface $recursiveTypeMapper
     * @return bool
     */
    public function canExtendTypeForClass(string $className, MutableObjectType $type, RecursiveTypeMapperInterface $recursiveTypeMapper): bool
    {
        return false;
    }

    /**
     * Extends the existing GraphQL type that is mapped to $className.
     *
     * @param string $className
     * @param MutableObjectType $type
     * @param RecursiveTypeMapperInterface $recursiveTypeMapper
     * @throws CannotMapTypeExceptionInterface
     */
    public function extendTypeForClass(string $className, MutableObjectType $type, RecursiveTypeMapperInterface $recursiveTypeMapper): void
    {
        throw CannotMapTypeException::createForExtendType($className, $type);
    }

    /**
     * Returns true if this type mapper can extend an existing type for the $typeName GraphQL type
     *
     * @param string $typeName
     * @param MutableObjectType $type
     * @param RecursiveTypeMapperInterface $recursiveTypeMapper
     * @return bool
     */
    public function canExtendTypeForName(string $typeName, MutableObjectType $type, RecursiveTypeMapperInterface $recursiveTypeMapper): bool
    {
        return false;
    }

    /**
     * Extends the existing GraphQL type that is mapped to the $typeName GraphQL type.
     *
     * @param string $typeName
     * @param MutableObjectType $type
     * @param RecursiveTypeMapperInterface $recursiveTypeMapper
     * @throws CannotMapTypeExceptionInterface
     */
    public function extendTypeForName(string $typeName, MutableObjectType $type, RecursiveTypeMapperInterface $recursiveTypeMapper): void
    {
        throw CannotMapTypeException::createForExtendName($typeName, $type);
    }
}
