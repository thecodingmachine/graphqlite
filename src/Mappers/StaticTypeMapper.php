<?php


namespace TheCodingMachine\GraphQL\Controllers\Mappers;
use function array_keys;
use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\OutputType;
use TheCodingMachine\GraphQL\Controllers\Mappers\Interfaces\InterfacesResolverInterface;

/**
 * A simple implementation of the TypeMapperInterface that expects mapping to be passed in a setter.
 *
 * Note: no constructor argument as this results in a loop most of the time.
 */
final class StaticTypeMapper implements TypeMapperInterface
{
    /**
     * @var array<string,OutputType>
     */
    private $types;

    /**
     * An array mapping a fully qualified class name to the matching TypeInterface
     *
     * @param array<string,OutputType> $types
     */
    public function setTypes(array $types)
    {
        $this->types = $types;
    }

    /**
     * @var array<string,InputType>
     */
    private $inputTypes;

    /**
     * An array mapping a fully qualified class name to the matching InputTypeInterface
     *
     * @param array<string,InputType> $inputTypes
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
     * @param RecursiveTypeMapperInterface $recursiveTypeMapper
     * @return OutputType
     * @throws CannotMapTypeException
     */
    public function mapClassToType(string $className, RecursiveTypeMapperInterface $recursiveTypeMapper): OutputType
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
     * @return InputType
     * @throws CannotMapTypeException
     */
    public function mapClassToInputType(string $className): InputType
    {
        if (isset($this->inputTypes[$className])) {
            return $this->inputTypes[$className];
        }
        throw CannotMapTypeException::createForInputType($className);
    }
}