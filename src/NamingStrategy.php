<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use TheCodingMachine\GraphQLite\Annotations\Factory;
use TheCodingMachine\GraphQLite\Annotations\Type;
use function lcfirst;
use function str_replace;
use function strlen;
use function strpos;
use function strrpos;
use function substr;

class NamingStrategy implements NamingStrategyInterface
{
    /**
     * Returns the name of the GraphQL interface from a name of a concrete class (when the interface is created
     * automatically to manage inheritance)
     */
    public function getInterfaceNameFromConcreteName(string $concreteType): string
    {
        return $concreteType . 'Interface';
    }

    /**
     * Returns the name of the GraphQL object from a name of GraphQL interface type (when the object is created
     * automatically from a "Type" annotated interface)
     */
    public function getConcreteNameFromInterfaceName(string $name): string
    {
        return str_replace('Interface', '', $name) . 'Impl';
    }

    /**
     * Returns the GraphQL output object type name based on the type className and the Type annotation.
     */
    public function getOutputTypeName(string $typeClassName, Type $type): string
    {
        $name = $type->getName();
        if ($name !== null) {
            return $name;
        }

        $prevPos = strrpos($typeClassName, '\\');
        if ($prevPos) {
            $typeClassName = substr($typeClassName, $prevPos + 1);
        }
        // By default, if the class name ends with Type, let's take the name of the class for the type
        if (! $type->isSelfType() && substr($typeClassName, -4) === 'Type') {
            return substr($typeClassName, 0, -4);
        }
        // Else, let's take the name of the targeted class
        $typeClassName = $type->getClass();
        $prevPos       = strrpos($typeClassName, '\\');
        if ($prevPos) {
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
        $prevPos = strrpos($className, '\\');
        if ($prevPos) {
            $className = substr($className, $prevPos + 1);
        }

        return $className . 'Input';
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
