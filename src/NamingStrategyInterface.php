<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use TheCodingMachine\GraphQLite\Annotations\Factory;
use TheCodingMachine\GraphQLite\Annotations\Type;

/**
 * @unstable See https://graphqlite.thecodingmachine.io/docs/semver.html
 */
interface NamingStrategyInterface
{
    /**
     * Returns the name of the GraphQL interface from a name of GraphQL concrete type (when the interface is created
     * automatically to manage inheritance)
     */
    public function getInterfaceNameFromConcreteName(string $concreteType): string;

    /**
     * Returns the name of the GraphQL object from a name of GraphQL interface type (when the object is created
     * automatically from a "Type" annotated interface)
     */
    public function getConcreteNameFromInterfaceName(string $name): string;

    /**
     * Returns the GraphQL output object type name based on the type className and the Type annotation.
     */
    public function getOutputTypeName(string $typeClassName, Type $type): string;

    public function getInputTypeName(string $className, Factory $factory): string;

    /**
     * Returns the name of a GraphQL field from the name of the annotated method.
     */
    public function getFieldNameFromMethodName(string $methodName): string;
}
