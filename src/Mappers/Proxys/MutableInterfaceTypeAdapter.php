<?php

namespace TheCodingMachine\GraphQLite\Mappers\Proxys;

use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\ObjectType;
use TheCodingMachine\GraphQLite\Types\MutableInterfaceType;

/**
 * An adapter class (actually a proxy) that adds the "mutable" feature to any Webonyx ObjectType.
 *
 * @internal
 */
final class MutableInterfaceTypeAdapter extends MutableInterfaceType
{
    /** @use MutableAdapterTrait */
    use MutableAdapterTrait;

    public function __construct(InterfaceType $type, string $className = null)
    {
        $this->type = $type;
        $this->className = $className;
        $this->name = $type->name;
        $this->config = $type->config;
        $this->astNode = $type->astNode;
        $this->extensionASTNodes = $type->extensionASTNodes;
    }
}
