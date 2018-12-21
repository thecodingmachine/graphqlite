<?php


namespace TheCodingMachine\GraphQL\Controllers;


use TheCodingMachine\GraphQL\Controllers\Annotations\Factory;
use TheCodingMachine\GraphQL\Controllers\Annotations\Type;

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

    /**
     * Returns the GraphQL output object type name based on the type className and the Type annotation.
     */
    public function getOutputTypeName(string $typeClassName, Type $type): string
    {
        if ($prevPos = strrpos($typeClassName, '\\')) {
            $typeClassName = substr($typeClassName, $prevPos + 1);
        }
        // By default, if the class name ends with Type, let's take the name of the class for the type
        if (substr($typeClassName, -4) === 'Type') {
            return substr($typeClassName, 0, -4);
        }
        // Else, let's take the name of the targeted class
        $typeClassName = $type->getClass();
        if ($prevPos = strrpos($typeClassName, '\\')) {
            $typeClassName = substr($typeClassName, $prevPos + 1);
        }
        return $typeClassName;
    }

    public function getInputTypeName(string $className, Factory $factory): string
    {
        $inputTypeName = $factory->getName();
        if ($inputTypeName !== null) {
            return $inputTypeName;
        }
        if ($prevPos = strrpos($className, '\\')) {
            $className = substr($className, $prevPos + 1);
        }
        return $className.'Input';
    }
}
