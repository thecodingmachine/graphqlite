<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Mappers\Root;

use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\NamedType;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\Type as GraphQLType;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Null_;
use phpDocumentor\Reflection\Types\Nullable;
use phpDocumentor\Reflection\Types\Object_;
use ReflectionMethod;
use ReflectionProperty;
use TheCodingMachine\GraphQLite\Undefined;

use function array_map;
use function array_values;
use function iterator_to_array;
use function mb_ltrim;

/**
 * A root type mapper for {@see Undefined} that maps replaces those with `null` as if Undefined wasn't part of the type at all.
 */
class UndefinedTypeMapper implements RootTypeMapperInterface
{
    public function __construct(
        private readonly RootTypeMapperInterface $next,
    ) {
    }

    public function toGraphQLOutputType(Type $type, OutputType|null $subType, ReflectionMethod|ReflectionProperty $reflector, DocBlock $docBlockObj): OutputType&GraphQLType
    {
        return $this->next->toGraphQLOutputType($type, $subType, $reflector, $docBlockObj);
    }

    public function toGraphQLInputType(Type $type, InputType|null $subType, string $argumentName, ReflectionMethod|ReflectionProperty $reflector, DocBlock $docBlockObj): InputType&GraphQLType
    {
        $type = self::replaceUndefinedWith($type);

        return $this->next->toGraphQLInputType($type, $subType, $argumentName, $reflector, $docBlockObj);
    }

    public function mapNameToType(string $typeName): NamedType&GraphQLType
    {
        return $this->next->mapNameToType($typeName);
    }

    /**
     * Replaces types like this: `int|Undefined` to `int|null`
     */
    public static function replaceUndefinedWith(Type $type, Type $replaceWith = new Null_()): Type
    {
        if ($type instanceof Object_ && mb_ltrim((string) $type->getFqsen(), '\\') === Undefined::class) {
            return $replaceWith;
        }

        if ($type instanceof Nullable) {
            return new Nullable(self::replaceUndefinedWith($type->getActualType(), $replaceWith));
        }

        if ($type instanceof Compound) {
            $types = array_map(static fn (Type $type) => self::replaceUndefinedWith($type, $replaceWith), iterator_to_array($type));

            return new Compound(array_values($types));
        }

        return $type;
    }

    public static function containsUndefined(Type $type): bool
    {
        return (string) $type !== (string) self::replaceUndefinedWith($type);
    }
}
