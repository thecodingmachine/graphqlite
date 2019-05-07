<?php


namespace TheCodingMachine\GraphQLite\Types;

use function array_map;
use function get_class;
use GraphQL\Type\Definition\IDType;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\LeafType;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use InvalidArgumentException;
use function is_array;
use TheCodingMachine\GraphQLite\Hydrators\HydratorInterface;

/**
 * Resolves arguments based on input value and InputType
 */
class ArgumentResolver
{
    /**
     * @var HydratorInterface
     */
    private $hydrator;

    public function __construct(HydratorInterface $hydrator)
    {
        $this->hydrator = $hydrator;
    }

    /**
     * Casts a value received from GraphQL into an argument passed to a method.
     *
     * @param mixed $val
     * @param InputType $type
     * @return mixed
     */
    public function resolve($source, $val, $context, ResolveInfo $resolveInfo, InputType $type)
    {
        $type = $this->stripNonNullType($type);
        if ($type instanceof ListOfType) {
            if (!is_array($val)) {
                throw new InvalidArgumentException('Expected GraphQL List but value passed is not an array.');
            }
            return array_map(function($item) use ($type, $source, $context, $resolveInfo) {
                return $this->resolve($source, $item, $context, $resolveInfo, $type->getWrappedType());
            }, $val);
        } elseif ($type instanceof IDType) {
            return new ID($val);
        } elseif ($type instanceof LeafType) {
            return $type->parseValue($val);
        } elseif ($type instanceof InputObjectType) {
            // TODO: can we get rid of HydratorInterface? Since the ResolvableInputInterface seems to be as good.
            return $this->hydrator->hydrate($source, $val, $context, $resolveInfo, $type);
        } else {
            throw new \RuntimeException('Unexpected type: '.get_class($type));
        }
    }

    private function stripNonNullType(Type $type): Type
    {
        if ($type instanceof NonNull) {
            return $this->stripNonNullType($type->getWrappedType());
        }
        return $type;
    }
}
