<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Types;

use GraphQL\Error\Error;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\IDType;
use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\LeafType;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use InvalidArgumentException;
use RuntimeException;

use function array_map;
use function assert;
use function is_array;

/**
 * Resolves arguments based on input value and InputType
 */
class ArgumentResolver
{
    /**
     * Casts a value received from GraphQL into an argument passed to a method.*
     *
     * @throws Error
     */
    public function resolve(object|null $source, mixed $val, mixed $context, ResolveInfo $resolveInfo, InputType&Type $type): mixed
    {
        $type = $this->stripNonNullType($type);
        if ($type instanceof ListOfType) {
            if (! is_array($val)) {
                throw new InvalidArgumentException('Expected GraphQL List but value passed is not an array.');
            }

            return array_map(function ($item) use ($type, $source, $context, $resolveInfo) {
                $wrappedType = $type->getWrappedType();
                assert($wrappedType instanceof InputType);
                return $this->resolve($source, $item, $context, $resolveInfo, $wrappedType);
            }, $val);
        }

        if ($type instanceof IDType) {
            return new ID($val);
        }

        // For some reason, the enum type behaves differently as the LeafType.
        // If seems to be already resolved.
        if ($type instanceof EnumType) {
            return $val;
        }

        if ($type instanceof LeafType) {
            return $type->parseValue($val);
        }

        if ($type instanceof ResolvableMutableInputInterface) {
            return $type->resolve($source, $val, $context, $resolveInfo);
        }

        throw new RuntimeException('Unexpected type: ' . $type::class);
    }

    private function stripNonNullType(InputType&Type $type): InputType&Type
    {
        if ($type instanceof NonNull) {
            $wrapped = $type->getWrappedType();
            assert($wrapped instanceof InputType);
            return $this->stripNonNullType($wrapped);
        }

        return $type;
    }
}
