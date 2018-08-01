<?php


namespace TheCodingMachine\GraphQL\Controllers\Mappers;
use Youshido\GraphQL\Type\InputTypeInterface;
use Youshido\GraphQL\Type\TypeInterface;

/**
 * A simple implementation of the TypeMapperInterface that expects mapping to be passed in a setter.
 *
 * Note: no constructor argument as this results in a loop most of the time.
 */
final class StaticTypeMapper implements TypeMapperInterface
{
    /**
     * @var array<string,TypeInterface>
     */
    private $types;

    /**
     * An array mapping a fully qualified class name to the matching TypeInterface
     *
     * @param array<string,TypeInterface> $types
     */
    public function setTypes(array $types)
    {
        $this->types = $types;
    }

    /**
     * @var array<string,InputTypeInterface>
     */
    private $inputTypes;

    /**
     * An array mapping a fully qualified class name to the matching InputTypeInterface
     *
     * @param array<string,InputTypeInterface> $inputTypes
     */
    public function setInputTypes(array $inputTypes)
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
     * @return TypeInterface
     * @throws CannotMapTypeException
     */
    public function mapClassToType(string $className): TypeInterface
    {
        if (isset($this->types[$className])) {
            return $this->types[$className];
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
        return isset($this->inputTypes[$className]);
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
        if (isset($this->inputTypes[$className])) {
            return $this->inputTypes[$className];
        }
        throw CannotMapTypeException::createForInputType($className);
    }
}