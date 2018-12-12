<?php


namespace TheCodingMachine\GraphQL\Controllers;

use function get_parent_class;
use GraphQL\Type\Definition\ObjectType;
use ReflectionClass;
use TheCodingMachine\GraphQL\Controllers\Annotations\Type;
use TheCodingMachine\GraphQL\Controllers\Mappers\RecursiveTypeMapperInterface;

/**
 * This class is in charge of creating Webonix GraphQL types from annotated objects that do not extend the
 * Webonix ObjectType class.
 */
class TypeGenerator
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
     * @var array<string, ObjectType>
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
     * @param object $annotatedObject An object with a Type annotation.
     * @param RecursiveTypeMapperInterface $recursiveTypeMapper
     * @return ObjectType
     * @throws \ReflectionException
     */
    public function mapAnnotatedObject($annotatedObject, RecursiveTypeMapperInterface $recursiveTypeMapper): ObjectType
    {
        $refTypeClass = new \ReflectionClass($annotatedObject);

        $typeField = $this->annotationReader->getTypeAnnotation($refTypeClass);

        if ($typeField === null) {
            throw MissingAnnotationException::missingTypeException();
        }

        $typeName = $this->namingStrategy->getOutputTypeName($refTypeClass->getName(), $typeField);

        if (!isset($this->cache[$typeName])) {
            $this->cache[$typeName] = new ObjectType([
                'name' => $typeName,
                'fields' => function() use ($annotatedObject, $recursiveTypeMapper, $typeField) {
                    $parentClass = get_parent_class($typeField->getClass());
                    $parentType = null;
                    if ($parentClass !== false) {
                        if ($recursiveTypeMapper->canMapClassToType($parentClass)) {
                            $parentType = $recursiveTypeMapper->mapClassToType($parentClass);
                        }
                    }

                    $fieldProvider = $this->controllerQueryProviderFactory->buildQueryProvider($annotatedObject, $recursiveTypeMapper);
                    $fields = $fieldProvider->getFields();
                    if ($parentType !== null) {
                        $fields = $parentType->getFields() + $fields;
                    }
                    return $fields;
                },
                'interfaces' => function() use ($typeField, $recursiveTypeMapper) {
                    return $recursiveTypeMapper->findInterfaces($typeField->getClass());
                }
            ]);
        }

        return $this->cache[$typeName];
    }
}
