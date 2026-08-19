<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use TheCodingMachine\GraphQLite\Annotations\Factory;
use TheCodingMachine\GraphQLite\Annotations\Input;
use TheCodingMachine\GraphQLite\Annotations\TypeInterface;
use TheCodingMachine\GraphQLite\Utils\FieldAccessorPrefixes;

use function implode;
use function str_ends_with;
use function str_replace;
use function strrpos;
use function substr;

class NamingStrategy implements NamingStrategyInterface
{
    public function __construct(
        private readonly FieldAccessorPrefixes $fieldAccessorPrefixes = new FieldAccessorPrefixes(),
    ) {
    }

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
        return $this->fieldAccessorPrefixes->stripGetterPrefix($methodName);
    }

    /**
     * Returns the name of a GraphQL input field from the name of the annotated method.
     */
    public function getInputFieldNameFromMethodName(string $methodName): string
    {
        return $this->fieldAccessorPrefixes->stripSetterPrefix($methodName);
    }

    /**
     * Returns the name of a GraphQL union type based on the included types.
     *
     * @param string[] $typeNames The list of GraphQL type names
     */
    public function getUnionTypeName(array $typeNames): string
    {
        return 'Union' . implode('', $typeNames);
    }
}
