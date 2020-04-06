<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Mappers\Root;

use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\NamedType;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\Type as GraphQLType;
use MyCLabs\Enum\Enum;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Object_;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Contracts\Cache\CacheInterface;
use TheCodingMachine\GraphQLite\AnnotationReader;
use TheCodingMachine\GraphQLite\Types\MyCLabsEnumType;
use TheCodingMachine\GraphQLite\Utils\Namespaces\NS;
use function assert;
use function is_a;
use function ltrim;

/**
 * Maps an class extending MyCLabs enums to a GraphQL type
 */
class MyCLabsEnumTypeMapper implements RootTypeMapperInterface
{
    /** @var array<class-string<object>, EnumType> */
    private $cache = [];
    /** @var array<string, EnumType> */
    private $cacheByName = [];
    /** @var RootTypeMapperInterface */
    private $next;
    /** @var AnnotationReader */
    private $annotationReader;
    /** @var array|NS[] */
    private $namespaces;
    /** @var CacheInterface */
    private $cacheService;

    /**
     * @param NS[] $namespaces List of namespaces containing enums. Used when searching an enum by name.
     */
    public function __construct(RootTypeMapperInterface $next, AnnotationReader $annotationReader, CacheInterface $cacheService, array $namespaces)
    {
        $this->next = $next;
        $this->annotationReader = $annotationReader;
        $this->cacheService = $cacheService;
        $this->namespaces = $namespaces;
    }

    /**
     * @param (OutputType&GraphQLType)|null $subType
     *
     * @return OutputType&GraphQLType
     */
    public function toGraphQLOutputType(Type $type, ?OutputType $subType, ReflectionMethod $refMethod, DocBlock $docBlockObj): OutputType
    {
        $result = $this->map($type);
        if ($result === null) {
            return $this->next->toGraphQLOutputType($type, $subType, $refMethod, $docBlockObj);
        }

        return $result;
    }

    /**
     * @param (InputType&GraphQLType)|null $subType
     *
     * @return InputType&GraphQLType
     */
    public function toGraphQLInputType(Type $type, ?InputType $subType, string $argumentName, ReflectionMethod $refMethod, DocBlock $docBlockObj): InputType
    {
        $result = $this->map($type);
        if ($result === null) {
            return $this->next->toGraphQLInputType($type, $subType, $argumentName, $refMethod, $docBlockObj);
        }

        return $result;
    }

    private function map(Type $type): ?EnumType
    {
        if (! $type instanceof Object_) {
            return null;
        }
        $fqsen = $type->getFqsen();
        if ($fqsen === null) {
            return null;
        }
        /**
         * @var class-string<object>
         */
        $enumClass = (string) $fqsen;

        return $this->mapByClassName($enumClass);
    }

    /**
     * @param class-string<object> $enumClass
     */
    private function mapByClassName(string $enumClass): ?EnumType
    {
        if (! is_a($enumClass, Enum::class, true)) {
            return null;
        }
        /**
         * @var class-string<Enum>
         */
        $enumClass = ltrim($enumClass, '\\');
        if (isset($this->cache[$enumClass])) {
            return $this->cache[$enumClass];
        }

        $refClass = new ReflectionClass($enumClass);
        $type = new MyCLabsEnumType($enumClass, $this->getTypeName($refClass));
        return $this->cacheByName[$type->name] = $this->cache[$enumClass] = $type;
    }

    private function getTypeName(ReflectionClass $refClass): string
    {
        $enumType = $this->annotationReader->getEnumTypeAnnotation($refClass);
        if ($enumType !== null) {
            $name = $enumType->getName();
            if ($name !== null) {
                return $name;
            }
        }
        return $refClass->getShortName();
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
        // This is a hack to make sure "$schema->assertValid()" returns true.
        // The mapNameToType will fail if the mapByClassName method was not called before.
        // This is actually not an issue in real life scenarios where enum types are never queried by type name.
        if (isset($this->cacheByName[$typeName])) {
            return $this->cacheByName[$typeName];
        }

        $nameToClassMapping = $this->getNameToClassMapping();
        if (isset($this->nameToClassMapping[$typeName])) {
            $className = $nameToClassMapping[$typeName];
            $type = $this->mapByClassName($className);
            assert($type !== null);
            return $type;
        }

        /*if (strpos($typeName, 'MyCLabsEnum_') === 0) {
            $className = str_replace('__', '\\', substr($typeName, 12));

            $type = $this->mapByClassName($className);
            if ($type !== null) {
                return $type;
            }
        }*/

        return $this->next->mapNameToType($typeName);
    }

    /** @var array<string, class-string<Enum>> */
    private $nameToClassMapping;

    /**
     * Go through all classes in the defined namespaces and loads the cache.
     *
     * @return array<string, class-string<Enum>>
     */
    private function getNameToClassMapping(): array
    {
        if ($this->nameToClassMapping === null) {
            $this->nameToClassMapping = $this->cacheService->get('myclabsenum_name_to_class', function () {
                $nameToClassMapping = [];
                foreach ($this->namespaces as $ns) {
                    foreach ($ns->getClassList() as $className => $classRef) {
                        if (! $classRef->isSubclassOf(Enum::class)) {
                            continue;
                        }

                        $nameToClassMapping[$this->getTypeName($classRef)] = $className;
                    }
                }
                return $nameToClassMapping;
            });
        }

        return $this->nameToClassMapping;
    }
}
