<?php


namespace TheCodingMachine\GraphQL\Controllers\Mappers;


use Youshido\GraphQL\Type\InputTypeInterface;
use Youshido\GraphQL\Type\TypeInterface;

class CompositeTypeMapper implements TypeMapperInterface
{
    /**
     * @var TypeMapperInterface[]
     */
    private $typeMappers;

    public function __construct(array $typeMappers)
    {

        $this->typeMappers = $typeMappers;
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
     * @return TypeInterface
     * @throws CannotMapTypeException
     */
    public function mapClassToType(string $className): TypeInterface
    {
        foreach ($this->typeMappers as $typeMapper) {
            if ($typeMapper->canMapClassToType($className)) {
                return $typeMapper->mapClassToType($className);
            }
        }
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
     * @return InputTypeInterface
     * @throws CannotMapTypeException
     */
    public function mapClassToInputType(string $className): InputTypeInterface
    {
        foreach ($this->typeMappers as $typeMapper) {
            if ($typeMapper->canMapClassToInputType($className)) {
                return $typeMapper->mapClassToInputType($className);
            }
        }
        throw CannotMapTypeException::createForInputType($className);
    }
}
