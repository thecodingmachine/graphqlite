<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Mappers;

use ReflectionMethod;
use ReflectionProperty;
use RuntimeException;

use function sprintf;

class DuplicateMappingException extends RuntimeException
{
    public static function createForType(string $sourceClass, string $type1, string $type2): self
    {
        throw new self(sprintf("The class '%s' should be mapped by only one GraphQL type class. Two classes are pointing via the @Type annotation to this class: '%s' and '%s'", $sourceClass, $type1, $type2));
    }

    /**
     * @param class-string<object> $sourceClass
     * @param class-string<object> $className1
     * @param class-string<object> $className2
     */
    public static function createForFactory(string $sourceClass, string $className1, string $method1, string $className2, string $method2): self
    {
        throw new self(sprintf("The class '%s' should be mapped to only one GraphQL Input type. Two methods are pointing via the @Factory annotation to this class: '%s::%s' and '%s::%s'", $sourceClass, $className1, $method1, $className2, $method2));
    }

    public static function createForTypeName(string $type, string $sourceClass1, string $sourceClass2): self
    {
        throw new self(sprintf("The type '%s' is created by 2 different classes: '%s' and '%s'", $type, $sourceClass1, $sourceClass2));
    }

    /**
     * @param ReflectionMethod|ReflectionProperty $firstReflector
     * @param ReflectionMethod|ReflectionProperty $secondReflector
     *
     * @return static
     */
    public static function createForQuery(string $sourceClass, string $queryName, $firstReflector, $secondReflector): self
    {
        $firstName = sprintf('%s::%s', $firstReflector->getDeclaringClass()->getName(), $firstReflector->getName());
        if ($firstReflector instanceof ReflectionMethod) {
            $firstName .= '()';
        }

        $secondName = sprintf('%s::%s', $secondReflector->getDeclaringClass()->getName(), $secondReflector->getName());
        if ($secondReflector instanceof ReflectionMethod) {
            $secondName .= '()';
        }

        throw new self(sprintf("The query/mutation/field '%s' is declared twice in class '%s'. First in '%s', second in '%s'", $queryName, $sourceClass, $firstName, $secondName));
    }

    public static function createForQueryInTwoControllers(string $sourceClass1, string $sourceClass2, string $queryName): self
    {
        throw new self(sprintf("The query/mutation '%s' is declared twice: in class '%s' and in class '%s'", $queryName, $sourceClass1, $sourceClass2));
    }

    public static function createForQueryInOneMethod(string $queryName, ReflectionMethod $method): self
    {
        throw new self(sprintf("The query/mutation/field '%s' is declared twice in '%s::%s()'", $queryName, $method->getDeclaringClass()->getName(), $method->getName()));
    }

    public static function createForQueryInOneProperty(string $queryName, ReflectionProperty $property): self
    {
        throw new self(sprintf("The query/mutation/field '%s' is declared twice in '%s::%s'", $queryName, $property->getDeclaringClass()->getName(), $property->getName()));
    }

    /**
     * @return static
     */
    public static function createForInput(string $sourceClass): self
    {
        throw new self(sprintf("The class '%s' should be mapped to only one GraphQL Input type. Two default inputs are declared as default via @Input annotation.", $sourceClass));
    }
}
