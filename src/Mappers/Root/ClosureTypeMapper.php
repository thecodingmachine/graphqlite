<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Mappers\Root;

use Closure;
use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\NamedType;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\Type as GraphQLType;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Fqsen;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Callable_;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Object_;
use ReflectionMethod;
use ReflectionProperty;
use TheCodingMachine\GraphQLite\Mappers\CannotMapTypeException;

use function count;
use function iterator_to_array;

/**
 * This mapper maps callable types into their return types, so that fields can defer their execution.
 */
class ClosureTypeMapper implements RootTypeMapperInterface
{
    private Object_ $closureType;

    public function __construct(
        private readonly RootTypeMapperInterface $next,
        private readonly RootTypeMapperInterface $topRootTypeMapper,
    ) {
        $this->closureType = new Object_(new Fqsen('\\' . Closure::class));
    }

    public function toGraphQLOutputType(Type $type, OutputType|null $subType, ReflectionMethod|ReflectionProperty $reflector, DocBlock $docBlockObj): OutputType&GraphQLType
    {
        // This check exists because any string may be a callable (referring to a global function),
        // so if a string that looks like a callable is returned from a resolver, it will get wrapped
        // in `Deferred`, even though it wasn't supposed to be a deferred value. This could be fixed
        // by combining `QueryField`'s resolver and `CallableTypeMapper` into one place, but
        // that's not currently possible with GraphQLite's design.
        if ($type instanceof Callable_) {
            throw CannotMapTypeException::createForUnexpectedCallable();
        }

        if (! $type instanceof Compound || ! $type->contains($this->closureType)) {
            return $this->next->toGraphQLOutputType($type, $subType, $reflector, $docBlockObj);
        }

        $allTypes = iterator_to_array($type);

        if (count($allTypes) !== 2) {
            return $this->next->toGraphQLOutputType($type, $subType, $reflector, $docBlockObj);
        }

        $callableType = $this->findCallableType($allTypes);
        $returnType = $callableType?->getReturnType();

        if (! $returnType) {
            throw CannotMapTypeException::createForMissingClosureReturnType();
        }

        if ($callableType->getParameters()) {
            throw CannotMapTypeException::createForUnexpectedClosureParameters();
        }

        return $this->topRootTypeMapper->toGraphQLOutputType($returnType, null, $reflector, $docBlockObj);
    }

    public function toGraphQLInputType(Type $type, InputType|null $subType, string $argumentName, ReflectionMethod|ReflectionProperty $reflector, DocBlock $docBlockObj): InputType&GraphQLType
    {
        if (! $type instanceof Callable_) {
            return $this->next->toGraphQLInputType($type, $subType, $argumentName, $reflector, $docBlockObj);
        }

        throw CannotMapTypeException::createForClosureAsInput();
    }

    public function mapNameToType(string $typeName): NamedType&GraphQLType
    {
        return $this->next->mapNameToType($typeName);
    }

    /** @param array<int, Type> $types */
    private function findCallableType(array $types): Callable_|null
    {
        foreach ($types as $type) {
            if ($type instanceof Callable_) {
                return $type;
            }
        }

        return null;
    }
}
