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
use Webmozart\Assert\Assert;
use function array_map;
use function get_class;
use function is_array;

/**
 * Resolves arguments based on input value and InputType
 */
class ArgumentResolver
{
    /**
     * Casts a value received from GraphQL into an argument passed to a method.
     *
     * @param mixed $val
     * @param mixed $context
     * @param InputType&Type $type
     *
     * @return mixed
     *
     * @throws Error
     */
    public function resolve(?object $source, $val, $context, ResolveInfo $resolveInfo, InputType $type)
    {
        $type = $this->stripNonNullType($type);
        if ($type instanceof ListOfType) {
            if (! is_array($val)) {
                throw new InvalidArgumentException('Expected GraphQL List but value passed is not an array.');
            }

            return array_map(function ($item) use ($type, $source, $context, $resolveInfo) {
                $wrappedType = $type->getWrappedType();
                Assert::isInstanceOf($wrappedType, InputType::class);

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

        throw new RuntimeException('Unexpected type: ' . get_class($type));
    }

    private function stripNonNullType(Type $type): Type
    {
        if ($type instanceof NonNull) {
            return $this->stripNonNullType($type->getWrappedType());
        }

        return $type;
    }
}
