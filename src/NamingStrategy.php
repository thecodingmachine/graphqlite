<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use TheCodingMachine\GraphQLite\Annotations\Factory;
use TheCodingMachine\GraphQLite\Annotations\Input;
use TheCodingMachine\GraphQLite\Annotations\TypeInterface;

use function lcfirst;
use function str_ends_with;
use function str_replace;
use function str_starts_with;
use function strlen;
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
    public function getOutputTypeName(string $typeClassName, TypeInterface $type): string
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
        if (! $type->isSelfType() && str_ends_with($typeClassName, 'Type')) {
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

    public function getInputTypeName(string $className, Input|Factory $input): string
    {
        $inputTypeName = $input->getName();
        if ($inputTypeName !== null) {
            return $inputTypeName;
        }
        $prevPos = strrpos($className, '\\');
        if ($prevPos) {
            $className = substr($className, $prevPos + 1);
        }

        if (str_ends_with($className, 'Input')) {
            return $className;
        }

        return $className . 'Input';
    }

    /**
     * Returns the name of a GraphQL field from the name of the annotated method.
     */
    public function getFieldNameFromMethodName(string $methodName): string
    {
        // Let's remove any "get" or "is".
        if (str_starts_with($methodName, 'get') && strlen($methodName) > 3) {
            return lcfirst(substr($methodName, 3));
        }
        if (str_starts_with($methodName, 'is') && strlen($methodName) > 2) {
            return lcfirst(substr($methodName, 2));
        }

        return $methodName;
    }

    /**
     * Returns the name of a GraphQL input field from the name of the annotated method.
     */
    public function getInputFieldNameFromMethodName(string $methodName): string
    {
        if (str_starts_with($methodName, 'set') && strlen($methodName) > 3) {
            return lcfirst(substr($methodName, 3));
        }

        return $methodName;
    }
}
