<?php


namespace TheCodingMachine\GraphQL\Controllers\Mappers;

use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\Type;

/**
 * Maps a PHP class to a GraphQL type
 */
interface TypeMapperInterface
{
    /**
     * Returns true if this type mapper can map the $className FQCN to a GraphQL type.
     *
     * @param string $className
     * @return bool
     */
    public function canMapClassToType(string $className): bool;

    /**
     * Maps a PHP fully qualified class name to a GraphQL type.
     *
     * @param string $className
     * @return OutputType&Type
     * @throws CannotMapTypeException
     */
    public function mapClassToType(string $className): OutputType;

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
