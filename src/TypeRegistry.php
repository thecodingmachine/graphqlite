<?php


namespace TheCodingMachine\GraphQLite;

use function get_class;
use GraphQL\Type\Definition\NamedType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\Type;
use TheCodingMachine\GraphQLite\Types\MutableObjectType;

/**
 * A cache used to store already FULLY COMPUTED types.
 */
class TypeRegistry
{
    /**
     * @var array<string,NamedType&Type&(MutableObjectType|InterfaceType)>
     */
    private $outputTypes = [];

    /**
     * Registers a type.
     * IMPORTANT: the type MUST be fully computed (so ExtendType annotations must have ALREADY been applied to the tag)
     * ONLY THE RecursiveTypeMapper IS ALLOWED TO CALL THIS METHOD.
     *
     * @param NamedType&Type&(ObjectType|InterfaceType) $type
     */
    public function registerType(NamedType $type): void
    {
        if (isset($this->outputTypes[$type->name])) {
            throw new GraphQLException('Type "'.$type->name.'" is already registered');
        }
        $this->outputTypes[$type->name] = $type;
    }

    public function hasType(string $typeName): bool
    {
        return isset($this->outputTypes[$typeName]);
    }

    /**
     * @param string $typeName
     * @return NamedType&Type&(ObjectType|InterfaceType)
     */
    public function getType(string $typeName): NamedType
    {
        if (!isset($this->outputTypes[$typeName])) {
            throw new GraphQLException('Could not find type "'.$typeName.'" in registry');
        }
        return $this->outputTypes[$typeName];
    }

    public function getMutableObjectType(string $typeName): MutableObjectType
    {
        $type = $this->getType($typeName);
        if (!$type instanceof MutableObjectType) {
            throw new GraphQLException('Expected GraphQL type "'.$typeName.'" to be an MutableObjectType. Got a '.get_class($type));
        }
        return $type;
    }
}
