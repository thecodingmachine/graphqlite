<?php

namespace TheCodingMachine\GraphQL\Controllers;

interface NamingStrategyInterface
{
    /**
     * Returns the name of the GraphQL interface from a name of GraphQL concrete type (when the interface is created
     * automatically to manage inheritance)
     */
    public function getInterfaceNameFromConcreteName(string $concreteType): string;
}