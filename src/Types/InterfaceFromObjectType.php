<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Types;

use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\ObjectType;
use InvalidArgumentException;
use TheCodingMachine\GraphQLite\Mappers\RecursiveTypeMapperInterface;
use function get_class;
use function gettype;
use function is_object;

class InterfaceFromObjectType extends InterfaceType
{
    /**
     * @param string $name The name of the interface
     */
    public function __construct(string $name, ObjectType $type, ?ObjectType $subType, RecursiveTypeMapperInterface $typeMapper)
    {
        parent::__construct([
            'name' => $name,
            'fields' => static function () use ($type) {
                return $type->getFields();
            },
            'description' => $type->description,
            'resolveType' => static function ($value) use ($typeMapper, $subType) {
                if (! is_object($value)) {
                    throw new InvalidArgumentException('Expected object for resolveType. Got: "' . gettype($value) . '"');
                }

                $className = get_class($value);

                return $typeMapper->mapClassToType($className, $subType);
            },
        ]);
    }
}
