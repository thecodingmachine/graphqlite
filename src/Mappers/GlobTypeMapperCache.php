<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Mappers;

use ReflectionClass;
use function array_keys;

/**
 * The cached results of a GlobTypeMapper
 */
class GlobTypeMapperCache
{
    /** @var array<string,string> Maps a domain class to the GraphQL type annotated class */
    private $mapClassToTypeArray = [];
    /** @var array<string,string> Maps a GraphQL type name to the GraphQL type annotated class */
    private $mapNameToType = [];
    /** @var array<string,string[]> Maps a domain class to the factory method that creates the input type in the form [classname, methodName] */
    private $mapClassToFactory = [];
    /** @var array<string,string[]> Maps a GraphQL input type name to the factory method that creates the input type in the form [classname, methodName] */
    private $mapInputNameToFactory = [];
    /** @var array<string,array<int, callable&array>> Maps a GraphQL type name to one or many decorators (with the @Decorator annotation) */
    private $mapInputNameToDecorator = [];

    /**
     * Merges annotations of a given class in the global cache.
     *
     * @param ReflectionClass<object> $refClass
     */
    public function registerAnnotations(ReflectionClass $refClass, GlobAnnotationsCache $globAnnotationsCache): void
    {
        $className = $refClass->getName();

        $typeClassName = $globAnnotationsCache->getTypeClassName();
        if ($typeClassName !== null) {
            if (isset($this->mapClassToTypeArray[$typeClassName]) && $globAnnotationsCache->isDefault()) {
                throw DuplicateMappingException::createForType($typeClassName, $this->mapClassToTypeArray[$typeClassName], $className);
            }

            if ($globAnnotationsCache->isDefault()) {
                $objectClassName                             = $typeClassName;
                $this->mapClassToTypeArray[$objectClassName] = $className;
            }

            $typeName = $globAnnotationsCache->getTypeName();
            $this->mapNameToType[$typeName] = $className;
        }

        foreach ($globAnnotationsCache->getFactories() as $methodName => [$inputName, $inputClassName, $isDefault, $declaringClass]) {
            if ($isDefault) {
                if (isset($this->mapClassToFactory[$inputClassName])) {
                    throw DuplicateMappingException::createForFactory($inputClassName, $this->mapClassToFactory[$inputClassName][0], $this->mapClassToFactory[$inputClassName][1], $refClass->getName(), $methodName);
                }
            } else {
                // If this is not the default factory, let's not map the class name to the factory.
                $inputClassName = null;
            }

            $refArray = [$declaringClass, $methodName];
            if ($inputClassName !== null) {
                $this->mapClassToFactory[$inputClassName] = $refArray;
            }
            $this->mapInputNameToFactory[$inputName] = $refArray;
        }

        foreach ($globAnnotationsCache->getDecorators() as $methodName => [$inputName, $declaringClass]) {
            $this->mapInputNameToDecorator[$inputName][] = [$declaringClass, $methodName];
        }
    }

    public function getTypeByObjectClass(string $className): ?string
    {
        return $this->mapClassToTypeArray[$className] ?? null;
    }

    /**
     * @return string[]
     */
    public function getSupportedClasses(): array
    {
        return array_keys($this->mapClassToTypeArray);
    }

    public function getTypeByGraphQLTypeName(string $graphqlTypeName): ?string
    {
        return $this->mapNameToType[$graphqlTypeName] ?? null;
    }

    /**
     * @return string[] Maps a GraphQL input type name to the factory method that creates the input type in the form [classname, methodname]
     */
    public function getFactoryByGraphQLInputTypeName(string $graphqlTypeName): ?array
    {
        return $this->mapInputNameToFactory[$graphqlTypeName] ?? null;
    }

    /**
     * @return array<int, string[]>|null A pointer to the decorators methods [$className, $methodName] or null on cache miss
     */
    public function getDecorateByGraphQLInputTypeName(string $graphqlTypeName): ?array
    {
        return $this->mapInputNameToDecorator[$graphqlTypeName] ?? null;
    }

    /**
     * @return string[]|null A pointer to the factory [$className, $methodName] or null on cache miss
     */
    public function getFactoryByObjectClass(string $className): ?array
    {
        return $this->mapClassToFactory[$className] ?? null;
    }
}
