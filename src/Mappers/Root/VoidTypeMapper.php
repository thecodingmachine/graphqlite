<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Mappers\Root;

use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\NamedType;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\Type as GraphQLType;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Void_;
use ReflectionMethod;
use ReflectionProperty;
use TheCodingMachine\GraphQLite\Mappers\CannotMapTypeException;
use TheCodingMachine\GraphQLite\Types\VoidType;

class VoidTypeMapper implements RootTypeMapperInterface
{
    private static VoidType $voidType;

    public function __construct(
        private readonly RootTypeMapperInterface $next,
    )
    {
    }

    public function toGraphQLOutputType(Type $type, OutputType|null $subType, ReflectionMethod|ReflectionProperty $reflector, DocBlock $docBlockObj): OutputType&GraphQLType
    {
        if (! $type instanceof Void_) {
            return $this->next->toGraphQLOutputType($type, $subType, $reflector, $docBlockObj);
        }

        return self::getVoidType();
    }

    public function toGraphQLInputType(Type $type, InputType|null $subType, string $argumentName, ReflectionMethod|ReflectionProperty $reflector, DocBlock $docBlockObj): InputType&GraphQLType
    {
        if (! $type instanceof Void_) {
            return $this->next->toGraphQLInputType($type, $subType, $argumentName, $reflector, $docBlockObj);
        }

        throw CannotMapTypeException::mustBeOutputType(self::getVoidType()->name);
    }

    public function mapNameToType(string $typeName): NamedType&GraphQLType
    {
        return match ($typeName) {
            self::getVoidType()->name => self::getVoidType(),
            default => $this->next->mapNameToType($typeName),
        };
    }

    private static function getVoidType(): VoidType
    {
        return self::$voidType ??= new VoidType();
    }
}
