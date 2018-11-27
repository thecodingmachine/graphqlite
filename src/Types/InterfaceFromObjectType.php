<?php


namespace TheCodingMachine\GraphQL\Controllers\Types;


use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\OutputType;
use TheCodingMachine\GraphQL\Controllers\Mappers\RecursiveTypeMapperInterface;

class InterfaceFromObjectType extends \GraphQL\Type\Definition\InterfaceType
{
    /**
     * @param ObjectType $type
     * @param RecursiveTypeMapperInterface $typeMapper
     */
    public function __construct(ObjectType $type, RecursiveTypeMapperInterface $typeMapper)
    {
        $name = $type->name.'Interface';
        $fields = $type->getFields();

        parent::__construct([
            'name' => $name,
            'fields' => $fields,
            'description' => $type->description,
            'resolveType' => function($value) use ($typeMapper) {
                if (!is_object($value)) {
                    throw new \InvalidArgumentException('Expected object for resolveType. Got: "'.gettype($value).'"');
                }

                $className = get_class($value);

                return $typeMapper->mapClassToType($className);
            }
        ]);
    }
}
