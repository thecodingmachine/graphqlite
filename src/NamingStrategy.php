<?php


namespace TheCodingMachine\GraphQLite;


use function lcfirst;
use function strlen;
use function strpos;
use function substr;
use TheCodingMachine\GraphQLite\Annotations\Factory;
use TheCodingMachine\GraphQLite\Annotations\Type;

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
        if (!$type->isSelfType() && substr($typeClassName, -4) === 'Type') {
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

    /**
     * Returns the name of a GraphQL field from the name of the annotated method.
     */
    public function getFieldNameFromMethodName(string $methodName): string
    {
        // Let's remove any "get" or "is".
        if (strpos($methodName, 'get') === 0 && strlen($methodName) > 3) {
            return lcfirst(substr($methodName, 3));
        }
        if (strpos($methodName, 'is') === 0 && strlen($methodName) > 2) {
            return lcfirst(substr($methodName, 2));
        }
        return $methodName;
    }
}
