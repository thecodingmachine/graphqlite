<?php


namespace TheCodingMachine\GraphQL\Controllers\Mappers;


use function array_map;
use function array_merge;
use function array_unique;
use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\Type;
use function is_array;
use function iterator_to_array;

class CompositeTypeMapper implements TypeMapperInterface
{
    /**
     * @var TypeMapperInterface[]
     */
    private $typeMappers;

    /**
     * The cache of supported classes.
     *
     * @var string[]
     */
    private $supportedClasses;

    /**
     * @param TypeMapperInterface[] $typeMappers
     */
    public function __construct(iterable $typeMappers)
    {
        $this->typeMappers = is_array($typeMappers) ? $typeMappers: iterator_to_array($typeMappers);
    }

    /**
     * Returns true if this type mapper can map the $className FQCN to a GraphQL type.
     *
     * @param string $className
     * @return bool
     */
    public function canMapClassToType(string $className): bool
    {
        foreach ($this->typeMappers as $typeMapper) {
            if ($typeMapper->canMapClassToType($className)) {
                return true;
            }
        }
        return false;
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
        foreach ($this->typeMappers as $typeMapper) {
            if ($typeMapper->canMapClassToType($className)) {
                return $typeMapper->mapClassToType($className, $recursiveTypeMapper);
            }
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
        if ($this->supportedClasses === null) {
            $supportedClassesArrays = array_map(function(TypeMapperInterface $typeMapper) { return $typeMapper->getSupportedClasses(); }, $this->typeMappers);
            $this->supportedClasses = array_unique(array_merge(...$supportedClassesArrays));
        }
        return $this->supportedClasses;
    }

    /**
     * Returns true if this type mapper can map the $className FQCN to a GraphQL input type.
     *
     * @param string $className
     * @return bool
     */
    public function canMapClassToInputType(string $className): bool
    {
        foreach ($this->typeMappers as $typeMapper) {
            if ($typeMapper->canMapClassToInputType($className)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Maps a PHP fully qualified class name to a GraphQL input type.
     *
     * @param string $className
     * @return InputType
     * @throws CannotMapTypeException
     */
    public function mapClassToInputType(string $className): InputType
    {
        foreach ($this->typeMappers as $typeMapper) {
            if ($typeMapper->canMapClassToInputType($className)) {
                return $typeMapper->mapClassToInputType($className);
            }
        }
        throw CannotMapTypeException::createForInputType($className);
    }

    /**
     * Returns a GraphQL type by name (can be either an input or output type)
     *
     * @param string $typeName The name of the GraphQL type
     * @param RecursiveTypeMapperInterface $recursiveTypeMapper
     * @return Type&(InputType|OutputType)
     */
    public function mapNameToType(string $typeName, RecursiveTypeMapperInterface $recursiveTypeMapper): Type
    {
        foreach ($this->typeMappers as $typeMapper) {
            if ($typeMapper->canMapNameToType($typeName)) {
                return $typeMapper->mapNameToType($typeName, $recursiveTypeMapper);
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
        foreach ($this->typeMappers as $typeMapper) {
            if ($typeMapper->canMapNameToType($typeName)) {
                return true;
            }
        }
        return false;
    }
}
