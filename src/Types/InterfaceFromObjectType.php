<?php


namespace TheCodingMachine\GraphQLite\Types;


use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\OutputType;
use TheCodingMachine\GraphQLite\Mappers\RecursiveTypeMapperInterface;

class InterfaceFromObjectType extends \GraphQL\Type\Definition\InterfaceType
{
    /**
     * @param string $name The name of the interface
     * @param ObjectType $type
     * @param RecursiveTypeMapperInterface $typeMapper
     */
    public function __construct(string $name, ObjectType $type, ?ObjectType $subType, RecursiveTypeMapperInterface $typeMapper)
    {
        parent::__construct([
            'name' => $name,
            'fields' => function() use ($type) {
                return $type->getFields();
            },
            'description' => $type->description,
            'resolveType' => function($value) use ($typeMapper, $subType) {
                if (!is_object($value)) {
                    throw new \InvalidArgumentException('Expected object for resolveType. Got: "'.gettype($value).'"');
                }

                $className = get_class($value);

                return $typeMapper->mapClassToType($className, $subType);
            }
        ]);
    }
}
