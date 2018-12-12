<?php


namespace TheCodingMachine\GraphQL\Controllers;


class NamingStrategy implements NamingStrategyInterface
{
    /**
     * Returns the name of the GraphQL interface from a name of a concrete class (when the interface is created
     * automatically to manage inheritance)
     */
    public function getInterfaceNameFromConcreteName(string $concreteType): string
    {
        return $concreteType.'Interface';
    }
}