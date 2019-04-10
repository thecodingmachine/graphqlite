<?php


namespace TheCodingMachine\GraphQLite\Mappers;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\Type;
use TheCodingMachine\GraphQLite\Types\MutableObjectType;

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
     * @param (OutputType&MutableObjectType)|(OutputType&InterfaceType)|null $subType An optional sub-type if the main class is an iterator that needs to be typed.
     * @return MutableObjectType
     * @throws CannotMapTypeExceptionInterface
     */
    public function mapClassToType(string $className, ?OutputType $subType): MutableObjectType;

    /**
     * Maps a PHP fully qualified class name to a GraphQL interface (or returns null if no interface is found).
     *
     * @param string $className The exact class name to look for (this function does not look into parent classes).
     * @param (OutputType&ObjectType)|(OutputType&InterfaceType)|null $subType A subtype (if the main className is an iterator)
     * @return OutputType&Type
     * @throws CannotMapTypeExceptionInterface
     */
    public function mapClassToInterfaceOrType(string $className, ?OutputType $subType): OutputType;

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
     * @return InputObjectType
     * @throws CannotMapTypeExceptionInterface
     */
    public function mapClassToInputType(string $className): InputObjectType;

    /**
     * Returns an array containing all OutputTypes.
     * Needed for introspection because of interfaces.
     *
     * @return array<string, OutputType>
     */
    public function getOutputTypes(): array;

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
     * @return Type&(InputType|OutputType)
     */
    public function mapNameToType(string $typeName): Type;

    /**
     * Returns the closest parent that can be mapped, or null if nothing can be matched.
     *
     * @param string $className
     * @return string|null
     */
    public function findClosestMatchingParent(string $className): ?string;
}
