<?php


namespace TheCodingMachine\GraphQLite\Mappers\Root;


use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\NamedType;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\Type as GraphQLType;
use GraphQL\Upload\UploadType;
use function ltrim;
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
use TheCodingMachine\GraphQLite\GraphQLException;
use TheCodingMachine\GraphQLite\Mappers\RecursiveTypeMapperInterface;
use TheCodingMachine\GraphQLite\Types\CustomTypesRegistry;
use TheCodingMachine\GraphQLite\Types\DateTimeType;
use TheCodingMachine\GraphQLite\Types\ID;

/**
 * Casts base GraphQL types (scalar, lists, DateTime, ID, UploadedFileInterface.
 * Does not deal with nullable types => assumes nullable types have been handled BEFORE.
 * Does not deal with union types => assumes union types have been handled BEFORE.
 */
class BaseTypeMapper implements RootTypeMapperInterface
{
    /**
     * @var RecursiveTypeMapperInterface
     */
    private $recursiveTypeMapper;

    public function __construct(RecursiveTypeMapperInterface $recursiveTypeMapper)
    {
        $this->recursiveTypeMapper = $recursiveTypeMapper;
    }

    public function toGraphQLOutputType(Type $type, ?OutputType $subType, ReflectionMethod $refMethod, DocBlock $docBlockObj): ?OutputType
    {
        $mappedType = $this->mapBaseType($type);
        if ($mappedType !== null) {
            return $mappedType;
        }
        if ($type instanceof Array_) {
            return GraphQLType::listOf(GraphQLType::nonNull($this->toGraphQLOutputType($type->getValueType(), $subType, $refMethod, $docBlockObj)));
        }
        if ($type instanceof Object_) {
            $className = ltrim($type->getFqsen(), '\\');
            return $this->recursiveTypeMapper->mapClassToInterfaceOrType($className, $subType);
        }
        return null;
    }

    public function toGraphQLInputType(Type $type, ?InputType $subType, string $argumentName, ReflectionMethod $refMethod, DocBlock $docBlockObj): ?InputType
    {
        $mappedType = $this->mapBaseType($type);
        if ($mappedType !== null) {
            return $mappedType;
        }
        if ($type instanceof Array_) {
            return GraphQLType::listOf(GraphQLType::nonNull($this->toGraphQLInputType($type->getValueType(), $subType, $argumentName, $refMethod, $docBlockObj)));
        }
        if ($type instanceof Object_) {
            $className = ltrim($type->getFqsen(), '\\');
            return $this->recursiveTypeMapper->mapClassToInputType($className);
        }
        return null;
    }

    /**
     * Casts a Type to a GraphQL type.
     * Does not deal with nullable.
     *
     * @param Type $type
     * @return \GraphQL\Type\Definition\BooleanType|\GraphQL\Type\Definition\FloatType|\GraphQL\Type\Definition\IDType|\GraphQL\Type\Definition\IntType|\GraphQL\Type\Definition\StringType|\GraphQL\Upload\UploadType|DateTimeType|null
     */
    private function mapBaseType(Type $type)
    {
        if ($type instanceof Integer) {
            return GraphQLType::int();
        } elseif ($type instanceof String_) {
            return GraphQLType::string();
        } elseif ($type instanceof Boolean) {
            return GraphQLType::boolean();
        } elseif ($type instanceof Float_) {
            return GraphQLType::float();
        } elseif ($type instanceof Object_) {
            $fqcn = (string) $type->getFqsen();
            switch ($fqcn) {
                case '\\DateTimeImmutable':
                case '\\DateTimeInterface':
                    return self::getDateTimeType();
                case '\\'.UploadedFileInterface::class:
                    return self::getUploadType();
                case '\\DateTime':
                    throw new GraphQLException('Type-hinting a parameter against DateTime is not allowed. Please use the DateTimeImmutable type instead.');
                case '\\'.ID::class:
                    return GraphQLType::id();
                default:
                    return null;
            }
        }
        return null;
    }

    private static $uploadType;

    private static function getUploadType(): UploadType
    {
        if (self::$uploadType === null) {
            self::$uploadType = new UploadType();
        }
        return self::$uploadType;
    }

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
     * @return NamedType|null
     */
    public function mapNameToType(string $typeName): ?NamedType
    {
        // No need to map base types, only types added by us.
        if ($typeName === 'Upload') {
            return self::getUploadType();
        }

        if ($typeName === 'DateTime') {
            return self::getDateTimeType();
        }

        return null;
    }
}
