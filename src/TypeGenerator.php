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
     * @var FieldsBuilderFactory
     */
    private $controllerQueryProviderFactory;
    /**
     * @var NamingStrategyInterface
     */
    private $namingStrategy;
    /**
     * @var TypeRegistry
     */
    private $typeRegistry;

    public function __construct(AnnotationReader $annotationReader,
                                FieldsBuilderFactory $controllerQueryProviderFactory,
                                NamingStrategyInterface $namingStrategy,
                                TypeRegistry $typeRegistry)
    {
        $this->annotationReader = $annotationReader;
        $this->controllerQueryProviderFactory = $controllerQueryProviderFactory;
        $this->namingStrategy = $namingStrategy;
        $this->typeRegistry = $typeRegistry;
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

        if ($this->typeRegistry->hasType($typeName)) {
            return $this->typeRegistry->getObjectType($typeName);
        }

        return new ObjectType([
            'name' => $typeName,
            'fields' => function() use ($annotatedObject, $recursiveTypeMapper, $typeField) {
                $parentClass = get_parent_class($typeField->getClass());
                $parentType = null;
                if ($parentClass !== false) {
                    if ($recursiveTypeMapper->canMapClassToType($parentClass)) {
                        $parentType = $recursiveTypeMapper->mapClassToType($parentClass, null);
                    }
                }

                $fieldProvider = $this->controllerQueryProviderFactory->buildFieldsBuilder($recursiveTypeMapper);
                $fields = $fieldProvider->getFields($annotatedObject);
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

    /**
     * @param object $annotatedObject An object with a ExtendType annotation.
     * @param ObjectType $type
     * @param RecursiveTypeMapperInterface $recursiveTypeMapper
     */
    public function extendAnnotatedObject($annotatedObject, ObjectType $type, RecursiveTypeMapperInterface $recursiveTypeMapper)
    {
        $refTypeClass = new \ReflectionClass($annotatedObject);

        $extendTypeAnnotation = $this->annotationReader->getExtendTypeAnnotation($refTypeClass);

        if ($extendTypeAnnotation === null) {
            throw MissingAnnotationException::missingExtendTypeException();
        }

        //$typeName = $this->namingStrategy->getOutputTypeName($refTypeClass->getName(), $extendTypeAnnotation);
        $typeName = $type->name;

        if ($this->typeRegistry->hasType($typeName)) {
            throw new GraphQLException(sprintf('Tried to extend GraphQL type "%s" that is already stored in the type registry.', $typeName));
        }

        return new ObjectType([
            'name' => $typeName,
            'fields' => function() use ($annotatedObject, $recursiveTypeMapper, $type) {
                /*$parentClass = get_parent_class($extendTypeAnnotation->getClass());
                $parentType = null;
                if ($parentClass !== false) {
                    if ($recursiveTypeMapper->canMapClassToType($parentClass)) {
                        $parentType = $recursiveTypeMapper->mapClassToType($parentClass, null);
                    }
                }*/

                $fieldProvider = $this->controllerQueryProviderFactory->buildFieldsBuilder($recursiveTypeMapper);
                $fields = $fieldProvider->getFields($annotatedObject);
                /*if ($parentType !== null) {
                    $fields = $parentType->getFields() + $fields;
                }*/

                $fields = $type->getFields() + $fields;

                return $fields;
            },
            'interfaces' => function() use ($type) {
                return $type->getInterfaces();
            }
        ]);
    }
}
