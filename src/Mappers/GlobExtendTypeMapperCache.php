<?php


namespace TheCodingMachine\GraphQLite\Mappers;

use function array_keys;
use ReflectionClass;
use TheCodingMachine\GraphQLite\Annotations\Type;

/**
 * The cached results of a GlobTypeMapper
 */
class GlobExtendTypeMapperCache
{
    /** @var array<string,array<string,string>> Maps a domain class to one or many type extenders (with the @ExtendType annotation) The array of type extenders has a key and value equals to FQCN */
    private $mapClassToExtendTypeArray = [];
    /** @var array<string,array<string,string>> Maps a GraphQL type name to one or many type extenders (with the @ExtendType annotation) The array of type extenders has a key and value equals to FQCN */
    private $mapNameToExtendType = [];

    /**
     * Merges annotations of a given class in the global cache.
     *
     * @param ReflectionClass $refClass
     * @param GlobAnnotationsCache $globExtendAnnotationsCache
     */
    public function registerAnnotations(ReflectionClass $refClass, GlobExtendAnnotationsCache $globExtendAnnotationsCache): void
    {
        $className = $refClass->getName();

        $typeClassName = $globExtendAnnotationsCache->getExtendTypeClassName();
        if ($typeClassName !== null) {
            $this->mapClassToExtendTypeArray[$typeClassName][$className] = $className;

            $typeName = $globExtendAnnotationsCache->getExtendTypeName();
            $this->mapNameToExtendType[$typeName][$className] = $className;
        }
    }

    /**
     * @return array<string,string>|null An array of classes with the @ExtendType annotation (key and value = FQCN)
     */
    public function getExtendTypesByObjectClass(string $className): ?array
    {
        return $this->mapClassToExtendTypeArray[$className] ?? null;
    }

    /**
     * @return array<string,string>|null An array of classes with the @ExtendType annotation (key and value = FQCN)
     */
    public function getExtendTypesByGraphQLTypeName(string $graphqlTypeName): ?array
    {
        return $this->mapNameToExtendType[$graphqlTypeName] ?? null;
    }
}
