<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Mappers\Root;

use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\NamedType;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\Type as GraphQLType;
use MyCLabs\Enum\Enum;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Object_;
use ReflectionClass;
use ReflectionEnum;
use ReflectionMethod;
use ReflectionProperty;
use TheCodingMachine\GraphQLite\AnnotationReader;
use TheCodingMachine\GraphQLite\Discovery\ClassFinder;
use TheCodingMachine\GraphQLite\Discovery\Cache\ClassFinderBoundCache;
use TheCodingMachine\GraphQLite\Reflection\DocBlock\DocBlockFactory;
use TheCodingMachine\GraphQLite\Types\EnumType;
use UnitEnum;
use function assert;
use function enum_exists;
use function ltrim;

/**
 * Maps an enum class to a GraphQL type (only available in PHP>=8.1)
 */
class EnumTypeMapper implements RootTypeMapperInterface
{
    /** @var array<class-string<UnitEnum>, EnumType> */
    private array $cacheByClass = [];
    /** @var array<string, EnumType> */
    private array $cacheByName = [];
    /** @var array<string, class-string<UnitEnum>> */
    private array $nameToClassMapping;

    public function __construct(
        private readonly RootTypeMapperInterface $next,
        private readonly AnnotationReader        $annotationReader,
        private readonly DocBlockFactory $docBlockFactory,
        private readonly ClassFinder             $classFinder,
        private readonly ClassFinderBoundCache   $classFinderBoundCache,
    ) {
    }

    /** @param (OutputType&GraphQLType)|null $subType */
    public function toGraphQLOutputType(
        Type $type,
        OutputType|null $subType,
        ReflectionMethod|ReflectionProperty $reflector,
        DocBlock $docBlockObj,
    ): OutputType&GraphQLType {
        $result = $this->map($type);
        return $result ?? $this->next->toGraphQLOutputType($type, $subType, $reflector, $docBlockObj);
    }

    /**
     * Maps into the appropriate InputType
     */
    public function toGraphQLInputType(
        Type $type,
        InputType|null $subType,
        string $argumentName,
        ReflectionMethod|ReflectionProperty $reflector,
        DocBlock $docBlockObj,
    ): InputType&GraphQLType
    {
        $result = $this->map($type);
        if ($result === null) {
            return $this->next->toGraphQLInputType($type, $subType, $argumentName, $reflector, $docBlockObj);
        }

        return $result;
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

        /** @var class-string<object> $enumClass */
        $enumClass = (string) $fqsen;

        return $this->mapByClassName($enumClass);
    }

    /** @param class-string $enumClass */
    private function mapByClassName(string $enumClass): EnumType|null
    {
        if (! enum_exists($enumClass)) {
            return null;
        }
        /** @var class-string<Enum> $enumClass */
        $enumClass = ltrim($enumClass, '\\');
        if (isset($this->cacheByClass[$enumClass])) {
            return $this->cacheByClass[$enumClass];
        }

        // phpcs:disable SlevomatCodingStandard.Commenting.InlineDocCommentDeclaration.MissingVariable
        /** @var class-string<UnitEnum> $enumClass */
        // phpcs:enable SlevomatCodingStandard.Commenting.InlineDocCommentDeclaration.MissingVariable

        $reflectionEnum = new ReflectionEnum($enumClass);

        $typeAnnotation = $this->annotationReader->getTypeAnnotation($reflectionEnum);
        $typeName = $typeAnnotation?->getName() ?? $reflectionEnum->getShortName();

        // Expose values instead of names if specifically configured to and if enum is string-backed
        $useValues = $typeAnnotation !== null &&
            $typeAnnotation->useEnumValues() &&
            $reflectionEnum->isBacked() &&
            (string) $reflectionEnum->getBackingType() === 'string';

        $enumDescription = $this->docBlockFactory
            ->createFromReflector($reflectionEnum)
            ->getSummary() ?: null;

        /** @var array<string, string> $enumCaseDescriptions */
        $enumCaseDescriptions = [];
        /** @var array<string, string> $enumCaseDeprecationReasons */
        $enumCaseDeprecationReasons = [];

        foreach ($reflectionEnum->getCases() as $reflectionEnumCase) {
            $docBlock = $this->docBlockFactory->createFromReflector($reflectionEnumCase);

            $enumCaseDescriptions[$reflectionEnumCase->getName()] = $docBlock->getSummary() ?: null;
            $deprecation = $docBlock->getTagsByName('deprecated')[0] ?? null;

            // phpcs:ignore
            if ($deprecation) {
                $enumCaseDeprecationReasons[$reflectionEnumCase->getName()] = (string) $deprecation;
            }
        }

        $type = new EnumType($enumClass, $typeName, $enumDescription, $enumCaseDescriptions, $enumCaseDeprecationReasons, $useValues);

        return $this->cacheByName[$type->name] = $this->cacheByClass[$enumClass] = $type;
    }

    private function getTypeName(ReflectionClass $reflectionClass): string
    {
        $typeAnnotation = $this->annotationReader->getTypeAnnotation($reflectionClass);

        return $typeAnnotation?->getName() ?? $reflectionClass->getShortName();
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

        return $this->next->mapNameToType($typeName);
    }

    /**
     * Go through all classes in the defined namespaces and loads the cache.
     *
     * @return array<string, class-string<UnitEnum>>
     */
    private function getNameToClassMapping(): array
    {
        $this->nameToClassMapping ??= $this->classFinderBoundCache->reduce(
           $this->classFinder,
           'enum_name_to_class',
           function (ReflectionClass $classReflection): ?array {
               if (! $classReflection->isEnum()) {
                   return null;
               }

               return [$this->getTypeName($classReflection) => $classReflection->getName()];
           },
           fn (array $entries) => array_merge(...array_values(array_filter($entries))),
       );

        return $this->nameToClassMapping;
    }
}
