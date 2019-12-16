<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Mappers;

use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use ReflectionClass;
use TheCodingMachine\GraphQLite\AnnotationReader;
use TheCodingMachine\GraphQLite\GraphQLRuntimeException;
use TheCodingMachine\GraphQLite\InputTypeGenerator;
use TheCodingMachine\GraphQLite\InputTypeUtils;
use TheCodingMachine\GraphQLite\NamingStrategyInterface;
use TheCodingMachine\GraphQLite\TypeGenerator;
use function class_exists;
use function implode;
use function interface_exists;
use function str_replace;

/**
 * A type mapper that is passed the list of classes that it must scan (unlike the GlobTypeMapper that find those automatically).
 */
final class StaticClassListTypeMapper extends AbstractTypeMapper
{
    /** @var array<int, string> The list of classes to be scanned. */
    private $classList;
    /**
     * The array of classes.
     * Key: fully qualified class name
     *
     * @var array<string,ReflectionClass<object>>
     */
    private $classes;

    /**
     * @param array<int, string> $classList The list of classes to analyze.
     */
    public function __construct(array $classList, TypeGenerator $typeGenerator, InputTypeGenerator $inputTypeGenerator, InputTypeUtils $inputTypeUtils, ContainerInterface $container, AnnotationReader $annotationReader, NamingStrategyInterface $namingStrategy, RecursiveTypeMapperInterface $recursiveTypeMapper, CacheInterface $cache, ?int $globTtl = 2, ?int $mapTtl = null)
    {
        $this->classList = $classList;
        $cachePrefix = str_replace(['\\', '{', '}', '(', ')', '/', '@', ':'], '_', implode('_', $classList));
        parent::__construct($cachePrefix, $typeGenerator, $inputTypeGenerator, $inputTypeUtils, $container, $annotationReader, $namingStrategy, $recursiveTypeMapper, $cache, $globTtl, $mapTtl);
    }

    /**
     * Returns the array of globbed classes.
     * Only instantiable classes are returned.
     *
     * @return array<string,ReflectionClass<object>> Key: fully qualified class name
     */
    protected function getClassList(): array
    {
        if ($this->classes === null) {
            $this->classes = [];
            foreach ($this->classList as $className) {
                if (! class_exists($className) && ! interface_exists($className)) {
                    throw new GraphQLRuntimeException('Could not find class "' . $className . '"');
                }
                $this->classes[$className] = new ReflectionClass($className);
            }
        }

        return $this->classes;
    }
}
