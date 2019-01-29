<?php


namespace TheCodingMachine\GraphQLite\Types;


use GraphQL\Type\Definition\ObjectType;
use TheCodingMachine\GraphQLite\Mappers\RecursiveTypeMapperInterface;

class UnionType extends \GraphQL\Type\Definition\UnionType
{
    /**
     * @param ObjectType[] $types
     * @param RecursiveTypeMapperInterface $typeMapper
     */
    public function __construct(array $types, RecursiveTypeMapperInterface $typeMapper)
    {
        $name = 'Union';
        foreach ($types as $type) {
            $name .= $type->name;
            if (!$type instanceof ObjectType) {
                throw InvalidTypesInUnionException::notObjectType();
            }
        }
        parent::__construct([
            'name' => $name,
            'types' => $types,
            'resolveType' => function($value) use ($typeMapper) {
                if (!is_object($value)) {
                    throw new \InvalidArgumentException('Expected object for resolveType. Got: "'.gettype($value).'"');
                }

                $className = get_class($value);
                return $typeMapper->mapClassToInterfaceOrType($className, null);
            }
        ]);
    }
}
