<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Types;

use GraphQL\Type\Definition\InterfaceType;

class ObjectFromInterfaceType extends MutableObjectType
{
    /**
     * @param string $name The name of the object type to create
     */
    public function __construct(string $name, InterfaceType $type)
    {
        parent::__construct([
            'name' => $name,
            'fields' => static function () use ($type) {
                return $type->getFields();
            },
            'interfaces' => [$type],
            'description' => $type->description,
        ]);
    }
}
