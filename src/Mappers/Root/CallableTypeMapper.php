<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Mappers\Root;

use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\NamedType;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\Type as GraphQLType;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Callable_;
use ReflectionMethod;
use ReflectionProperty;
use TheCodingMachine\GraphQLite\Mappers\CannotMapTypeException;

/**
 * This mapper maps callable types into their return types, so that fields can defer their execution.
 */
class CallableTypeMapper implements RootTypeMapperInterface
{
    public function __construct(
        private readonly RootTypeMapperInterface $next,
        private readonly RootTypeMapperInterface $topRootTypeMapper,
    ) {
    }

    public function toGraphQLOutputType(Type $type, OutputType|null $subType, ReflectionMethod|ReflectionProperty $reflector, DocBlock $docBlockObj): OutputType&GraphQLType
    {
        if (! $type instanceof Callable_) {
            return $this->next->toGraphQLOutputType($type, $subType, $reflector, $docBlockObj);
        }

        if ($type->getParameters()) {
            throw CannotMapTypeException::createForUnexpectedCallableParameters();
        }

        $returnType = $type->getReturnType();

        if (! $returnType) {
            throw CannotMapTypeException::createForMissingCallableReturnType();
        }

        return $this->topRootTypeMapper->toGraphQLOutputType($returnType, null, $reflector, $docBlockObj);
    }

    public function toGraphQLInputType(Type $type, InputType|null $subType, string $argumentName, ReflectionMethod|ReflectionProperty $reflector, DocBlock $docBlockObj): InputType&GraphQLType
    {
        if (! $type instanceof Callable_) {
            return $this->next->toGraphQLInputType($type, $subType, $argumentName, $reflector, $docBlockObj);
        }

        throw CannotMapTypeException::createForCallableAsInput();
    }

    public function mapNameToType(string $typeName): NamedType&GraphQLType
    {
        return $this->next->mapNameToType($typeName);
    }
}
