<?php


namespace TheCodingMachine\GraphQLite\Mappers;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\Type;
use TheCodingMachine\GraphQLite\Mappers\Interfaces\InterfacesResolverInterface;
use TheCodingMachine\GraphQLite\Types\MutableInputObjectType;
use TheCodingMachine\GraphQLite\Types\MutableObjectType;
use TheCodingMachine\GraphQLite\Types\ResolvableMutableInputInterface;
use TheCodingMachine\GraphQLite\Types\ResolvableMutableInputObjectType;

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
     * @param OutputType|null $subType An optional sub-type if the main class is an iterator that needs to be typed.
     * @return MutableObjectType
     * @throws CannotMapTypeExceptionInterface
     */
    public function mapClassToType(string $className, ?OutputType $subType): MutableObjectType;

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
     * Returns the list of classes that have matching GraphQL types.
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
     * @return ResolvableMutableInputInterface&InputObjectType
     */
    public function mapClassToInputType(string $className): ResolvableMutableInputInterface;

    /**
     * Returns true if this type mapper can extend an existing type for the $className FQCN
     *
     * @param string $className
     * @param MutableObjectType $type
     * @return bool
     */
    public function canExtendTypeForClass(string $className, MutableObjectType $type): bool;

    /**
     * Extends the existing GraphQL type that is mapped to $className.
     *
     * @param string $className
     * @param MutableObjectType $type
     * @throws CannotMapTypeExceptionInterface
     */
    public function extendTypeForClass(string $className, MutableObjectType $type): void;

    /**
     * Returns true if this type mapper can extend an existing type for the $typeName GraphQL type
     *
     * @param string $typeName
     * @param MutableObjectType $type
     * @return bool
     */
    public function canExtendTypeForName(string $typeName, MutableObjectType $type): bool;

    /**
     * Extends the existing GraphQL type that is mapped to the $typeName GraphQL type.
     *
     * @param string $typeName
     * @param MutableObjectType $type
     * @throws CannotMapTypeExceptionInterface
     */
    public function extendTypeForName(string $typeName, MutableObjectType $type): void;

    /**
     * Returns true if this type mapper can decorate an existing input type for the $typeName GraphQL input type
     *
     * @param string $typeName
     * @param ResolvableMutableInputInterface $type
     * @return bool
     */
    public function canDecorateInputTypeForName(string $typeName, ResolvableMutableInputInterface $type): bool;

    /**
     * Decorates the existing GraphQL input type that is mapped to the $typeName GraphQL input type.
     *
     * @param string $typeName
     * @param ResolvableMutableInputInterface $type
     * @throws CannotMapTypeExceptionInterface
     */
    public function decorateInputTypeForName(string $typeName, ResolvableMutableInputInterface $type): void;
}
