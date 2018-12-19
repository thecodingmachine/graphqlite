<?php


namespace TheCodingMachine\GraphQL\Controllers\Mappers;
use function array_keys;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\Type;
use TheCodingMachine\GraphQL\Controllers\Mappers\Interfaces\InterfacesResolverInterface;

/**
 * A simple implementation of the TypeMapperInterface that expects mapping to be passed in a setter.
 *
 * Note: no constructor argument as this results in a loop most of the time.
 */
final class StaticTypeMapper implements TypeMapperInterface
{
    /**
     * @var array<string,ObjectType>
     */
    private $types;

    /**
     * An array mapping a fully qualified class name to the matching TypeInterface
     *
     * @param array<string,ObjectType> $types
     */
    public function setTypes(array $types): void
    {
        $this->types = $types;
    }

    /**
     * @var array<string,InputType&Type>
     */
    private $inputTypes;

    /**
     * An array mapping a fully qualified class name to the matching InputTypeInterface
     *
     * @param array<string,InputType&Type> $inputTypes
     */
    public function setInputTypes(array $inputTypes): void
    {
        $this->inputTypes = $inputTypes;
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
     * @param RecursiveTypeMapperInterface $recursiveTypeMapper
     * @return ObjectType
     * @throws CannotMapTypeException
     */
    public function mapClassToType(string $className, RecursiveTypeMapperInterface $recursiveTypeMapper): ObjectType
    {
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
     * @throws CannotMapTypeException
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
     * @throws CannotMapTypeException
     */
    public function mapNameToType(string $typeName, RecursiveTypeMapperInterface $recursiveTypeMapper): Type
    {
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
        return false;
    }
}
