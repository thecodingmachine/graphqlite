<?php


namespace TheCodingMachine\GraphQLite\Hydrators;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use TheCodingMachine\GraphQLite\GraphQLException;
use TheCodingMachine\GraphQLite\Hydrators\HydratorInterface;
use TheCodingMachine\GraphQLite\Types\ResolvableInputInterface;
use TheCodingMachine\GraphQLite\Types\ResolvableInputObjectType;

/**
 * Hydrates input types based on the Factory annotation.
 */
class FactoryHydrator implements HydratorInterface
{

    /**
     * Hydrates/returns an object based on a PHP array and a GraphQL type.
     *
     * @param mixed[] $data
     * @param InputObjectType $type
     * @return object
     * @throws CannotHydrateException
     */
    public function hydrate($source, array $data, $context, ResolveInfo $resolveInfo, InputObjectType $type)
    {
        if ($type instanceof ResolvableInputInterface) {
            return $type->resolve($source, $data, $context, $resolveInfo);
        }
        throw CannotHydrateException::createForInputType($type->name);
    }

    /**
     * Whether this hydrate can hydrate the passed data.
     *
     * @param mixed[] $data
     * @param InputObjectType $type
     * @return bool
     */
    public function canHydrate(array $data, InputObjectType $type): bool
    {
        return $type instanceof ResolvableInputInterface;
    }
}
