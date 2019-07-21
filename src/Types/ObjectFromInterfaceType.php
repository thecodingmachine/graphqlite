<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Types;

use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\Type;
use InvalidArgumentException;
use TheCodingMachine\GraphQLite\Mappers\RecursiveTypeMapperInterface;
use function get_class;
use function gettype;
use function is_object;

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
            'interfaces' => [
                $type
            ],
            'description' => $type->description
        ]);
    }
}
