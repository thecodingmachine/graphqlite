<?php


namespace TheCodingMachine\GraphQL\Controllers\Mappers;

use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\Type;
use TheCodingMachine\GraphQL\Controllers\Mappers\Interfaces\InterfacesResolverInterface;

/**
 * Maps a PHP class to a GraphQL type
 */
interface TypeMapperInterface
{
    /**
     * Returns true if this type mapper can map the $className FQCN to a GraphQL type.
     *
     * @param string $className The exact class name to look for (this function does not look into parent classes).
     * @return bool
     */
    public function canMapClassToType(string $className): bool;

    /**
     * Maps a PHP fully qualified class name to a GraphQL type.
     *
     * @param string $className The exact class name to look for (this function does not look into parent classes).
     * @param RecursiveTypeMapperInterface $recursiveTypeMapper
     * @return ObjectType
     * @throws CannotMapTypeException
     */
    public function mapClassToType(string $className, RecursiveTypeMapperInterface $recursiveTypeMapper): ObjectType;

    /**
     * Returns true if this type mapper can map the $typeName GraphQL name to a GraphQL type.
     *
     * @param string $typeName The name of the GraphQL type
     * @return bool
     */
    public function canMapNameToType(string $typeName): bool;

    /**
     * Returns a GraphQL type by name (can be either an input or output type)
     *
     * @param string $typeName The name of the GraphQL type
     * @param RecursiveTypeMapperInterface $recursiveTypeMapper
     * @return Type&(InputType|OutputType)
     */
    public function mapNameToType(string $typeName, RecursiveTypeMapperInterface $recursiveTypeMapper): Type;

    /**
     * Returns the list of classes that have matching input GraphQL types.
     *
     * @return string[]
     */
    public function getSupportedClasses(): array;

    /**
     * Returns true if this type mapper can map the $className FQCN to a GraphQL input type.
     *
     * @param string $className
     * @return bool
     */
    public function canMapClassToInputType(string $className): bool;

    /**
     * Maps a PHP fully qualified class name to a GraphQL input type.
     *
     * @param string $className
     * @return InputType&Type
     * @throws CannotMapTypeException
     */
    public function mapClassToInputType(string $className): InputType;
}
