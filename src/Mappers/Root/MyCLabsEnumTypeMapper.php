<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Mappers\Root;

use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\NamedType;
use GraphQL\Type\Definition\OutputType;
use MyCLabs\Enum\Enum;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Object_;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
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
    private array $cache = [];
    /** @var array<string, EnumType> */
    private array $cacheByName = [];

    /** @var array<string, class-string<Enum>> */
    private array|null $nameToClassMapping = null;

    /** @param NS[] $namespaces List of namespaces containing enums. Used when searching an enum by name. */
    public function __construct(
        private readonly RootTypeMapperInterface $next,
        private readonly AnnotationReader $annotationReader,
        private readonly CacheInterface $cacheService,
        private readonly array $namespaces,
    ) {
    }

    public function toGraphQLOutputType(
        Type $type,
        OutputType|null $subType,
        ReflectionMethod|ReflectionProperty $reflector,
        DocBlock $docBlockObj,
    ): OutputType&\GraphQL\Type\Definition\Type
    {
        $result = $this->map($type);
        return $result ?? $this->next->toGraphQLOutputType($type, $subType, $reflector, $docBlockObj);
    }

    public function toGraphQLInputType(
        Type $type,
        InputType|null $subType,
        string $argumentName,
        ReflectionMethod|ReflectionProperty $reflector,
        DocBlock $docBlockObj,
    ): InputType&\GraphQL\Type\Definition\Type
    {
        $result = $this->map($type);
        return $result ?? $this->next->toGraphQLInputType($type, $subType, $argumentName, $reflector, $docBlockObj);
    }

    private function map(Type $type): EnumType|null
    {
        if (! $type instanceof Object_) {
            return null;
        }
        $fqsen = $type->getFqsen();
        if ($fqsen === null) {
            return null;
        }
        /** @var  class-string<object> $enumClass */
        $enumClass = (string) $fqsen;

        return $this->mapByClassName($enumClass);
    }

    /** @param class-string<object> $enumClass */
    private function mapByClassName(string $enumClass): EnumType|null
    {
        if (! is_a($enumClass, Enum::class, true)) {
            return null;
        }
        /** @var class-string<Enum> $enumClass */
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
    public function mapNameToType(string $typeName): NamedType&\GraphQL\Type\Definition\Type
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

                        /** @var class-string<Enum> $className */
                        $nameToClassMapping[$this->getTypeName($classRef)] = $className;
                    }
                }
                return $nameToClassMapping;
            });
        }

        return $this->nameToClassMapping;
    }
}
