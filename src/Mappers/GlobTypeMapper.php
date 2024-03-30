<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Mappers;

use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use ReflectionClass;
use TheCodingMachine\GraphQLite\AnnotationReader;
use TheCodingMachine\GraphQLite\Discovery\ClassFinder;
use TheCodingMachine\GraphQLite\InputTypeGenerator;
use TheCodingMachine\GraphQLite\InputTypeUtils;
use TheCodingMachine\GraphQLite\NamingStrategyInterface;
use TheCodingMachine\GraphQLite\TypeGenerator;

use function str_replace;

/**
 * Scans all the classes in a given namespace of the main project (not the vendor directory).
 * Analyzes all classes and uses the @Type annotation to find the types automatically.
 *
 * Assumes that the container contains a class whose identifier is the same as the class name.
 *
 * @internal
 */
final class GlobTypeMapper extends AbstractTypeMapper
{
    public function __construct(
        private ClassFinder $classFinder,
        TypeGenerator $typeGenerator,
        InputTypeGenerator $inputTypeGenerator,
        InputTypeUtils $inputTypeUtils,
        ContainerInterface $container,
        AnnotationReader $annotationReader,
        NamingStrategyInterface $namingStrategy,
        RecursiveTypeMapperInterface $recursiveTypeMapper,
        CacheInterface $cache,
        int|null $globTTL = 2,
        int|null $mapTTL = null,
    ) {
        parent::__construct(
            '',
            $typeGenerator,
            $inputTypeGenerator,
            $inputTypeUtils,
            $container,
            $annotationReader,
            $namingStrategy,
            $recursiveTypeMapper,
            $cache,
            $globTTL,
            $mapTTL,
        );
    }

    /**
     * Returns the array of globbed classes.
     * Only instantiable classes are returned.
     *
     * @return array<string,ReflectionClass<object>> Key: fully qualified class name
     */
    protected function getClassList(): array
    {
        return iterator_to_array($this->classFinder);
    }
}
