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
use TheCodingMachine\GraphQL\Controllers\Mappers\RecursiveTypeMapperInterface;

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

    public function __construct(AnnotationReader $annotationReader,
                                ControllerQueryProviderFactory $controllerQueryProviderFactory,
                                NamingStrategyInterface $namingStrategy)
    {
        $this->annotationReader = $annotationReader;
        $this->controllerQueryProviderFactory = $controllerQueryProviderFactory;
        $this->namingStrategy = $namingStrategy;
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

    public function mapFactoryMethod(ReflectionMethod $method, RecursiveTypeMapperInterface $recursiveTypeMapper): InputObjectType
    {
        [$inputName, $className] = $this->getInputTypeNameAndClassName($method);

        if (!isset($this->cache[$inputName])) {
            $this->cache[$inputName] = new InputObjectType([
                'name' => $inputName,
                // TODO: add description.
                'fields' => function() use ($method, $recursiveTypeMapper) {

                    $fieldProvider = $this->controllerQueryProviderFactory->buildQueryProvider($recursiveTypeMapper);
                    $fields = $fieldProvider->getInputFields($method);

                    return $fields;
                }
            ]);
        }

        return $this->cache[$inputName];
    }
}
