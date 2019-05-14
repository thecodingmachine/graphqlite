<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Hydrators;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use TheCodingMachine\GraphQLite\Types\ResolvableMutableInputInterface;

/**
 * Hydrates input types based on the Factory annotation.
 */
class FactoryHydrator implements HydratorInterface
{
    /**
     * Hydrates/returns an object based on a PHP array and a GraphQL type.
     *
     * @param mixed[] $data
     * @param mixed   $context
     *
     * @throws CannotHydrateException
     */
    public function hydrate(?object $source, array $data, $context, ResolveInfo $resolveInfo, InputObjectType $type) : object
    {
        if ($type instanceof ResolvableMutableInputInterface) {
            return $type->resolve($source, $data, $context, $resolveInfo);
        }
        throw CannotHydrateException::createForInputType($type->name);
    }

    /**
     * Whether this hydrate can hydrate the passed data.
     *
     * @param mixed[] $data
     */
    public function canHydrate(array $data, InputObjectType $type) : bool
    {
        return $type instanceof ResolvableMutableInputInterface;
    }
}
