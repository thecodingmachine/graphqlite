<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\NamedType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use TheCodingMachine\GraphQLite\Types\MutableObjectType;
use TheCodingMachine\GraphQLite\Types\ResolvableMutableInputInterface;
use function get_class;

/**
 * A cache used to store already FULLY COMPUTED types.
 */
class TypeRegistry
{
    /** @var array<string,NamedType&Type&(MutableObjectType|InterfaceType|(InputObjectType&ResolvableMutableInputInterface))> */
    private $outputTypes = [];

    /**
     * Registers a type.
     * IMPORTANT: the type MUST be fully computed (so ExtendType annotations must have ALREADY been applied to the tag)
     * ONLY THE RecursiveTypeMapper IS ALLOWED TO CALL THIS METHOD.
     *
     * @param NamedType&Type&(MutableObjectType|InterfaceType|(InputObjectType&ResolvableMutableInputInterface)) $type
     */
    public function registerType(NamedType $type): void
    {
        if (isset($this->outputTypes[$type->name])) {
            throw new GraphQLException('Type "' . $type->name . '" is already registered');
        }
        $this->outputTypes[$type->name] = $type;
    }

    public function hasType(string $typeName): bool
    {
        return isset($this->outputTypes[$typeName]);
    }

    /**
     * @return NamedType&Type&(ObjectType|InterfaceType|(InputObjectType&ResolvableMutableInputInterface))
     */
    public function getType(string $typeName): NamedType
    {
        if (! isset($this->outputTypes[$typeName])) {
            throw new GraphQLException('Could not find type "' . $typeName . '" in registry');
        }

        return $this->outputTypes[$typeName];
    }

    public function getMutableObjectType(string $typeName): MutableObjectType
    {
        $type = $this->getType($typeName);
        if (! $type instanceof MutableObjectType) {
            throw new GraphQLException('Expected GraphQL type "' . $typeName . '" to be an MutableObjectType. Got a ' . get_class($type));
        }

        return $type;
    }
}
