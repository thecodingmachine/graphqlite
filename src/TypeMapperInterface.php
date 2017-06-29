<?php


namespace TheCodingMachine\GraphQL\Controllers;
use Youshido\GraphQL\Type\TypeInterface;

/**
 * Maps a PHP class to a GraphQL type
 */
interface TypeMapperInterface
{
    /**
     * Maps a PHP fully qualified class name to a GraphQL type.
     *
     * @param string $className
     * @return TypeInterface
     */
    public function mapClassToType(string $className): TypeInterface;
}
