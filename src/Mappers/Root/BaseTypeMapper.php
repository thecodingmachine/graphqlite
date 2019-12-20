<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Mappers\Root;

use GraphQL\Type\Definition\BooleanType;
use GraphQL\Type\Definition\FloatType;
use GraphQL\Type\Definition\IDType;
use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\IntType;
use GraphQL\Type\Definition\NamedType;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\StringType;
use GraphQL\Type\Definition\Type as GraphQLType;
use GraphQL\Upload\UploadType;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Boolean;
use phpDocumentor\Reflection\Types\Float_;
use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Types\Object_;
use phpDocumentor\Reflection\Types\String_;
use Psr\Http\Message\UploadedFileInterface;
use ReflectionMethod;
use TheCodingMachine\GraphQLite\Mappers\CannotMapTypeException;
use TheCodingMachine\GraphQLite\Mappers\CannotMapTypeExceptionInterface;
use TheCodingMachine\GraphQLite\Mappers\RecursiveTypeMapperInterface;
use TheCodingMachine\GraphQLite\Types\DateTimeType;
use TheCodingMachine\GraphQLite\Types\ID;
use function ltrim;

/**
 * Casts base GraphQL types (scalar, lists, DateTime, ID, UploadedFileInterface.
 * Does not deal with nullable types => assumes nullable types have been handled BEFORE.
 * Does not deal with union types => assumes union types have been handled BEFORE.
 */
class BaseTypeMapper implements RootTypeMapperInterface
{
    /** @var RecursiveTypeMapperInterface */
    private $recursiveTypeMapper;
    /** @var RootTypeMapperInterface */
    private $topRootTypeMapper;
    /** @var RootTypeMapperInterface */
    private $next;

    public function __construct(RootTypeMapperInterface $next, RecursiveTypeMapperInterface $recursiveTypeMapper, RootTypeMapperInterface $topRootTypeMapper)
    {
        $this->recursiveTypeMapper = $recursiveTypeMapper;
        $this->topRootTypeMapper = $topRootTypeMapper;
        $this->next = $next;
    }

    /**
     * @param (OutputType&GraphQLType)|null $subType
     *
     * @return OutputType&GraphQLType
     *
     * @throws CannotMapTypeExceptionInterface
     */
    public function toGraphQLOutputType(Type $type, ?OutputType $subType, ReflectionMethod $refMethod, DocBlock $docBlockObj): OutputType
    {
        $mappedType = $this->mapBaseType($type);
        if ($mappedType !== null) {
            return $mappedType;
        }

        if ($type instanceof Array_) {
            $innerType = $this->topRootTypeMapper->toGraphQLOutputType($type->getValueType(), $subType, $refMethod, $docBlockObj);
            /*if ($innerType === null) {
                return $this->next->toGraphQLOutputType($type, $subType, $refMethod, $docBlockObj);
            }*/

            return GraphQLType::listOf($innerType);
        }
        if ($type instanceof Object_) {
            $className = ltrim((string) $type->getFqsen(), '\\');

            return $this->recursiveTypeMapper->mapClassToInterfaceOrType($className, $subType);
        }

        return $this->next->toGraphQLOutputType($type, $subType, $refMethod, $docBlockObj);
    }

    /**
     * @param (InputType&GraphQLType)|null $subType
     *
     * @return InputType&GraphQLType
     */
    public function toGraphQLInputType(Type $type, ?InputType $subType, string $argumentName, ReflectionMethod $refMethod, DocBlock $docBlockObj): InputType
    {
        $mappedType = $this->mapBaseType($type);
        if ($mappedType !== null) {
            return $mappedType;
        }
        if ($type instanceof Array_) {
            $innerType = $this->topRootTypeMapper->toGraphQLInputType($type->getValueType(), $subType, $argumentName, $refMethod, $docBlockObj);
            /*if ($innerType === null) {
                return $this->next->toGraphQLInputType($type, $subType, $argumentName, $refMethod, $docBlockObj);
            }*/

            return GraphQLType::listOf($innerType);
        }
        if ($type instanceof Object_) {
            $className = ltrim((string) $type->getFqsen(), '\\');

            return $this->recursiveTypeMapper->mapClassToInputType($className);
        }

        return $this->next->toGraphQLInputType($type, $subType, $argumentName, $refMethod, $docBlockObj);
    }

    /**
     * Casts a Type to a GraphQL type.
     * Does not deal with nullable.
     *
     * @return BooleanType|FloatType|IDType|IntType|StringType|UploadType|DateTimeType|null
     */
    private function mapBaseType(Type $type)
    {
        if ($type instanceof Integer) {
            return GraphQLType::int();
        }

        if ($type instanceof String_) {
            return GraphQLType::string();
        }

        if ($type instanceof Boolean) {
            return GraphQLType::boolean();
        }

        if ($type instanceof Float_) {
            return GraphQLType::float();
        }

        if ($type instanceof Object_) {
            $fqcn = (string) $type->getFqsen();
            switch ($fqcn) {
                case '\\DateTimeImmutable':
                case '\\DateTimeInterface':
                    return self::getDateTimeType();
                case '\\' . UploadedFileInterface::class:
                    return self::getUploadType();
                case '\\DateTime':
                    throw CannotMapTypeException::createForDateTime();
                case '\\' . ID::class:
                    return GraphQLType::id();
                default:
                    return null;
            }
        }

        return null;
    }

    /** @var UploadType */
    private static $uploadType;

    private static function getUploadType(): UploadType
    {
        if (self::$uploadType === null) {
            self::$uploadType = new UploadType();
        }

        return self::$uploadType;
    }

    /** @var DateTimeType */
    private static $dateTimeType;

    private static function getDateTimeType(): DateTimeType
    {
        if (self::$dateTimeType === null) {
            self::$dateTimeType = new DateTimeType();
        }

        return self::$dateTimeType;
    }

    /**
     * Returns a GraphQL type by name.
     * If this root type mapper can return this type in "toGraphQLOutputType" or "toGraphQLInputType", it should
     * also map these types by name in the "mapNameToType" method.
     *
     * @param string $typeName The name of the GraphQL type
     */
    public function mapNameToType(string $typeName): NamedType
    {
        // No need to map base types, only types added by us.
        if ($typeName === 'Upload') {
            return self::getUploadType();
        }

        if ($typeName === 'DateTime') {
            return self::getDateTimeType();
        }

        return $this->next->mapNameToType($typeName);
    }
}
