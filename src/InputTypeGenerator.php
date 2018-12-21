<?php


namespace TheCodingMachine\GraphQL\Controllers;

use function get_parent_class;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ObjectType;
use phpDocumentor\Reflection\Fqsen;
use phpDocumentor\Reflection\Types\Object_;
use ReflectionClass;
use ReflectionMethod;
use ReflectionType;
use TheCodingMachine\GraphQL\Controllers\Annotations\Type;
use TheCodingMachine\GraphQL\Controllers\Hydrators\HydratorInterface;
use TheCodingMachine\GraphQL\Controllers\Mappers\RecursiveTypeMapperInterface;
use TheCodingMachine\GraphQL\Controllers\Types\ResolvableInputObjectType;

/**
 * This class is in charge of creating Webonix InputTypes from Factory annotations.
 */
class InputTypeGenerator
{
    /**
     * @var AnnotationReader
     */
    private $annotationReader;
    /**
     * @var ControllerQueryProviderFactory
     */
    private $controllerQueryProviderFactory;
    /**
     * @var array<string, InputObjectType>
     */
    private $cache = [];
    /**
     * @var NamingStrategyInterface
     */
    private $namingStrategy;
    /**
     * @var HydratorInterface
     */
    private $hydrator;

    public function __construct(AnnotationReader $annotationReader,
                                ControllerQueryProviderFactory $controllerQueryProviderFactory,
                                NamingStrategyInterface $namingStrategy,
                                HydratorInterface $hydrator)
    {
        $this->annotationReader = $annotationReader;
        $this->controllerQueryProviderFactory = $controllerQueryProviderFactory;
        $this->namingStrategy = $namingStrategy;
        $this->hydrator = $hydrator;
    }

    /**
     * Returns an array with 2 elements: [ $inputName, $className ]
     *
     * @param ReflectionMethod $method
     * @return string[]
     */
    public function getInputTypeNameAndClassName(ReflectionMethod $method): array
    {
        $fqsen = ltrim((string) $this->validateReturnType($method), '\\');
        $factory = $this->annotationReader->getFactoryAnnotation($method);
        if ($factory === null) {
            throw new \RuntimeException($method->getDeclaringClass()->getName().'::'.$method->getName().' has no @Factory annotation.');
        }
        return [$this->namingStrategy->getInputTypeName($fqsen, $factory), $fqsen];
    }

    private function validateReturnType(ReflectionMethod $refMethod): Fqsen
    {
        $returnType = $refMethod->getReturnType();
        if ($returnType === null) {
            throw MissingTypeHintException::missingReturnType($refMethod);
        }

        if ($returnType->allowsNull()) {
            throw MissingTypeHintException::nullableReturnType($refMethod);
        }

        $type = (string) $returnType;

        $typeResolver = new \phpDocumentor\Reflection\TypeResolver();

        $phpdocType = $typeResolver->resolve($type);
        if (!$phpdocType instanceof Object_) {
            throw MissingTypeHintException::invalidReturnType($refMethod);
        }

        return $phpdocType->getFqsen();
    }

    /**
     * @param object $factory
     * @param string $methodName
     * @param RecursiveTypeMapperInterface $recursiveTypeMapper
     * @return InputObjectType
     */
    public function mapFactoryMethod($factory, string $methodName, RecursiveTypeMapperInterface $recursiveTypeMapper): InputObjectType
    {
        $method = new ReflectionMethod($factory, $methodName);

        [$inputName, $className] = $this->getInputTypeNameAndClassName($method);

        if (!isset($this->cache[$inputName])) {
            // TODO: add comment argument.
            $this->cache[$inputName] = new ResolvableInputObjectType($inputName, $this->controllerQueryProviderFactory, $recursiveTypeMapper, $factory, $methodName, $this->hydrator, null);
        }

        return $this->cache[$inputName];
    }
}
