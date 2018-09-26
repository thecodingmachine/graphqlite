<?php


namespace TheCodingMachine\GraphQL\Controllers\Mappers;


use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\OutputType;
use Youshido\GraphQL\Type\InputTypeInterface;
use Youshido\GraphQL\Type\TypeInterface;

class CompositeTypeMapper implements TypeMapperInterface
{
    /**
     * @var TypeMapperInterface[]
     */
    private $typeMappers;

    /**
     * @param TypeMapperInterface[] $typeMappers
     */
    public function setTypeMappers(array $typeMappers): void
    {
        // TODO: move the setter in the constructor in v3
        // We can do this if we get rid of the Registry god object which is easier if we don't have to inject it in the
        // AbsractAnnotatedObjectType class (this class will disappear in v3)
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
     * @return OutputType
     * @throws CannotMapTypeException
     */
    public function mapClassToType(string $className): OutputType
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
}
