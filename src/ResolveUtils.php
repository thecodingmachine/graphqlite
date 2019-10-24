<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use function is_array;
use function is_iterable;
use function is_object;

/**
 * Utility class to assert resolved values against a type.
 *
 * @internal
 */
class ResolveUtils
{
    /**
     * @param mixed $result
     */
    public static function assertInnerReturnType($result, Type $type): void
    {
        if ($type instanceof NonNull && $result === null) {
            throw TypeMismatchRuntimeException::unexpectedNullValue();
        }
        if ($result === null) {
            return;
        }
        $type = self::removeNonNull($type);
        if ($type instanceof ListOfType) {
            if (! is_iterable($result)) {
                throw TypeMismatchRuntimeException::expectedIterable($result);
            }
            // If this is an array, we can scan it and check the types.
            if (is_array($result)) {
                foreach ($result as $item) {
                    self::assertInnerReturnType($item, $type->getWrappedType());
                }
            }
            // TODO: if this is an iterable (not an array, we might want to wrap the iterable in another
            // iterable that checks the type.
        }
        if (! ($type instanceof ObjectType)) {
            return;
        }

        if (! is_object($result)) {
            throw TypeMismatchRuntimeException::expectedObject($result);
        }
        // TODO: it would be great to check if this is the actual object type we were expecting
    }

    private static function removeNonNull(Type $type): Type
    {
        if ($type instanceof NonNull) {
            return $type->getWrappedType();
        }

        return $type;
    }
}
