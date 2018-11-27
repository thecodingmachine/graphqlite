<?php


namespace TheCodingMachine\GraphQL\Controllers\Mappers;

use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\Type;

/**
 * Maps a PHP class to a GraphQL type.
 *
 * Unlike the TypeMapperInterface, if a given class does not map a type, parent classes are explored.
 */
interface RecursiveTypeMapperInterface
{
    /**
     * Returns true if this type mapper can map the $className FQCN to a GraphQL type.
     *
     * @param string $className The class name to look for (this function looks into parent classes if the class does not match a type).
     * @return bool
     */
    public function canMapClassToType(string $className): bool;

    /**
     * Maps a PHP fully qualified class name to a GraphQL type.
     *
     * @param string $className The class name to look for (this function looks into parent classes if the class does not match a type).
     * @return OutputType&Type
     * @throws CannotMapTypeException
     */
    public function mapClassToType(string $className): OutputType;

    /**
     * Maps a PHP fully qualified class name to a GraphQL interface (or returns null if no interface is found).
     *
     * @param string $className The exact class name to look for (this function does not look into parent classes).
     * @return OutputType&Type
     * @throws CannotMapTypeException
     */
    public function mapClassToInterfaceOrType(string $className): OutputType;

    /**
     * Finds the list of interfaces returned by $className.
     *
     * @param string $className
     * @return InterfaceType[]
     */
    public function findInterfaces(string $className): array;

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
