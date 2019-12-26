<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\NamedType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use TheCodingMachine\GraphQLite\Types\MutableInterface;
use TheCodingMachine\GraphQLite\Types\MutableInterfaceType;
use TheCodingMachine\GraphQLite\Types\MutableObjectType;
use TheCodingMachine\GraphQLite\Types\ResolvableMutableInputInterface;
use TheCodingMachine\GraphQLite\Types\UnionType;
use function get_class;

/**
 * A cache used to store already FULLY COMPUTED types.
 */
class TypeRegistry
{
    /** @var array<string,NamedType&Type&(MutableObjectType|InterfaceType|UnionType|(InputObjectType&ResolvableMutableInputInterface))> */
    private $types = [];

    /**
     * Registers a type.
     * IMPORTANT: the type MUST be fully computed (so ExtendType annotations must have ALREADY been applied to the tag)
     * ONLY THE RecursiveTypeMapper IS ALLOWED TO CALL THIS METHOD.
     *
     * @param NamedType&Type&(MutableObjectType|InterfaceType|UnionType|(InputObjectType&ResolvableMutableInputInterface)) $type
     */
    public function registerType(NamedType $type): void
    {
        if (isset($this->types[$type->name])) {
            throw new GraphQLRuntimeException('Type "' . $type->name . '" is already registered');
        }
        $this->types[$type->name] = $type;
    }

    /**
     * A failsafe variant of registerType:
     * - Registers the type passed in parameter.
     * - If the type is already present, does not fail. Instead, return the old type already available.
     *
     * @param NamedType&Type&(MutableObjectType|InterfaceType|UnionType|(InputObjectType&ResolvableMutableInputInterface)) $type
     *
     * @return NamedType&Type&(MutableObjectType|InterfaceType|UnionType|(InputObjectType&ResolvableMutableInputInterface))
     */
    public function getOrRegisterType(NamedType $type): NamedType
    {
        if (isset($this->types[$type->name])) {
            return $this->types[$type->name];
        }
        $this->types[$type->name] = $type;

        return $type;
    }

    public function hasType(string $typeName): bool
    {
        return isset($this->types[$typeName]);
    }

    /**
     * @return NamedType&Type&(ObjectType|InterfaceType|UnionType|(InputObjectType&ResolvableMutableInputInterface))
     */
    public function getType(string $typeName): NamedType
    {
        if (! isset($this->types[$typeName])) {
            throw new GraphQLRuntimeException('Could not find type "' . $typeName . '" in registry');
        }

        return $this->types[$typeName];
    }

    public function getMutableObjectType(string $typeName): MutableObjectType
    {
        $type = $this->getType($typeName);
        if (! $type instanceof MutableObjectType) {
            throw new GraphQLRuntimeException('Expected GraphQL type "' . $typeName . '" to be an MutableObjectType. Got a ' . get_class($type));
        }

        return $type;
    }

    /**
     * @return MutableInterface&(MutableObjectType|MutableInterfaceType)
     */
    public function getMutableInterface(string $typeName): MutableInterface
    {
        $type = $this->getType($typeName);
        if (! $type instanceof MutableInterface || (! $type instanceof MutableInterfaceType && ! $type instanceof MutableObjectType)) {
            throw new GraphQLRuntimeException('Expected GraphQL type "' . $typeName . '" to be either a MutableObjectType or a MutableInterfaceType. Got a ' . get_class($type));
        }

        return $type;
    }
}
