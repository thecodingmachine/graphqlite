<?php


namespace TheCodingMachine\GraphQL\Controllers\Mappers;


use function get_parent_class;
use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\Type;

/**
 * This class wraps a TypeMapperInterface into a RecursiveTypeMapperInterface.
 * While the wrapped class does only tests one given class, the recursive type mapper
 * tests the class and all its parents.
 */
class RecursiveTypeMapper implements RecursiveTypeMapperInterface
{
    /**
     * @var TypeMapperInterface
     */
    private $typeMapper;

    public function __construct(TypeMapperInterface $typeMapper)
    {
        $this->typeMapper = $typeMapper;
    }

    /**
     * Returns true if this type mapper can map the $className FQCN to a GraphQL type.
     *
     * @param string $className The class name to look for (this function looks into parent classes if the class does not match a type).
     * @return bool
     */
    public function canMapClassToType(string $className): bool
    {
        do {
            if ($this->typeMapper->canMapClassToType($className)) {
                return true;
            }
        } while ($className = get_parent_class($className));
        return false;
    }

    /**
     * Maps a PHP fully qualified class name to a GraphQL type.
     *
     * @param string $className The class name to look for (this function looks into parent classes if the class does not match a type).
     * @return OutputType&Type
     * @throws CannotMapTypeException
     */
    public function mapClassToType(string $className): OutputType
    {
        do {
            if ($this->typeMapper->canMapClassToType($className)) {
                return $this->typeMapper->mapClassToType($className);
            }
        } while ($className = get_parent_class($className));
        throw CannotMapTypeException::createForType($className);
    }

    /**
     * Returns true if this type mapper can map the $className FQCN to a GraphQL input type.
     *
     * @param string $className
     * @return bool
     */
    public function canMapClassToInputType(string $className): bool
    {
        return $this->typeMapper->canMapClassToInputType($className);
    }

    /**
     * Maps a PHP fully qualified class name to a GraphQL input type.
     *
     * @param string $className
     * @return InputType&Type
     * @throws CannotMapTypeException
     */
    public function mapClassToInputType(string $className): InputType
    {
        return $this->typeMapper->mapClassToInputType($className);
    }
}
