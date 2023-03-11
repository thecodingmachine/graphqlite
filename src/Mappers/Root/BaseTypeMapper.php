<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Mappers\Root;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use GraphQL\Type\Definition\BooleanType;
use GraphQL\Type\Definition\FloatType;
use GraphQL\Type\Definition\IDType;
use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\IntType;
use GraphQL\Type\Definition\NamedType;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Type\Definition\StringType;
use GraphQL\Type\Definition\Type as GraphQLType;
use GraphQL\Upload\UploadType;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\AbstractList;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Boolean;
use phpDocumentor\Reflection\Types\Float_;
use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Types\Object_;
use phpDocumentor\Reflection\Types\String_;
use Psr\Http\Message\UploadedFileInterface;
use ReflectionMethod;
use ReflectionProperty;
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
    public function __construct(private readonly RootTypeMapperInterface $next, private readonly RecursiveTypeMapperInterface $recursiveTypeMapper, private readonly RootTypeMapperInterface $topRootTypeMapper)
    {
    }

    /** @throws CannotMapTypeExceptionInterface */
    public function toGraphQLOutputType(Type $type, OutputType|null $subType, ReflectionMethod|ReflectionProperty $reflector, DocBlock $docBlockObj): OutputType&GraphQLType
    {
        $mappedType = $this->mapBaseType($type);
        if ($mappedType !== null) {
            return $mappedType;
        }

        if ($type instanceof AbstractList) {
            $innerType = $this->topRootTypeMapper->toGraphQLOutputType($type->getValueType(), $subType, $reflector, $docBlockObj);
            /*if ($innerType === null) {
                return $this->next->toGraphQLOutputType($type, $subType, $reflector, $docBlockObj);
            }*/

            return GraphQLType::listOf($innerType);
        }
        if ($type instanceof Object_) {
            $className = ltrim((string) $type->getFqsen(), '\\');

            return $this->recursiveTypeMapper->mapClassToInterfaceOrType($className, $subType);
        }

        return $this->next->toGraphQLOutputType($type, $subType, $reflector, $docBlockObj);
    }

    /**
     * @throws CannotMapTypeException
     * @throws CannotMapTypeExceptionInterface
     */
    public function toGraphQLInputType(Type $type, InputType|null $subType, string $argumentName, ReflectionMethod|ReflectionProperty $reflector, DocBlock $docBlockObj): InputType&GraphQLType
    {
        $mappedType = $this->mapBaseType($type);
        if ($mappedType !== null) {
            return $mappedType;
        }
        if ($type instanceof Array_) {
            $innerType = $this->topRootTypeMapper->toGraphQLInputType($type->getValueType(), $subType, $argumentName, $reflector, $docBlockObj);
            /*if ($innerType === null) {
                return $this->next->toGraphQLInputType($type, $subType, $argumentName, $reflector, $docBlockObj);
            }*/

            return GraphQLType::listOf($innerType);
        }
        if ($type instanceof Object_) {
            $className = ltrim((string) $type->getFqsen(), '\\');

            return $this->recursiveTypeMapper->mapClassToInputType($className);
        }

        return $this->next->toGraphQLInputType($type, $subType, $argumentName, $reflector, $docBlockObj);
    }

    /**
     * Casts a Type to a GraphQL type.
     * Does not deal with nullable.
     *
     * @throws CannotMapTypeException
     */
    private function mapBaseType(Type $type): BooleanType|FloatType|IDType|IntType|StringType|UploadType|DateTimeType|ScalarType|null
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

            return match ($fqcn) {
                '\\' . DateTimeImmutable::class,
                '\\' . DateTimeInterface::class => self::getDateTimeType(),

                '\\' . UploadedFileInterface::class => self::getUploadType(),

                '\\' . DateTime::class => throw CannotMapTypeException::createForDateTime(),

                '\\' . ID::class => GraphQLType::id(),

                default => null,
            };
        }

        return null;
    }
    private static UploadType|null $uploadType = null;

    private static function getUploadType(): UploadType
    {
        if (self::$uploadType === null) {
            self::$uploadType = new UploadType();
        }

        return self::$uploadType;
    }
    private static DateTimeType|null $dateTimeType = null;

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
    public function mapNameToType(string $typeName): NamedType&GraphQLType
    {
        // No need to map base types, only types added by us.
        return match ($typeName) {
            'Upload' => self::getUploadType(),
            'DateTime' => self::getDateTimeType(),
            default => $this->next->mapNameToType($typeName),
        };
    }
}
