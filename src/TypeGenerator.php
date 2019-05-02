<?php


namespace TheCodingMachine\GraphQLite;

use function get_parent_class;
use GraphQL\Type\Definition\ObjectType;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use TheCodingMachine\GraphQLite\Annotations\Type;
use TheCodingMachine\GraphQLite\Mappers\RecursiveTypeMapperInterface;
use TheCodingMachine\GraphQLite\Types\MutableObjectType;
use TheCodingMachine\GraphQLite\Types\TypeAnnotatedObjectType;

/**
 * This class is in charge of creating Webonyx GraphQL types from annotated objects that do not extend the
 * Webonyx ObjectType class.
 */
class TypeGenerator
{
    /**
     * @var AnnotationReader
     */
    private $annotationReader;
    /**
     * @var FieldsBuilder
     */
    private $fieldsBuilder;
    /**
     * @var NamingStrategyInterface
     */
    private $namingStrategy;
    /**
     * @var TypeRegistry
     */
    private $typeRegistry;
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var RecursiveTypeMapperInterface
     */
    private $recursiveTypeMapper;

    public function __construct(AnnotationReader $annotationReader,
                                NamingStrategyInterface $namingStrategy,
                                TypeRegistry $typeRegistry,
                                ContainerInterface $container)
    {
        $this->annotationReader = $annotationReader;
        $this->namingStrategy = $namingStrategy;
        $this->typeRegistry = $typeRegistry;
        $this->container = $container;
    }

    public function setRecursiveTypeMapper(RecursiveTypeMapperInterface $recursiveTypeMapper)
    {
        $this->recursiveTypeMapper = $recursiveTypeMapper;
    }

    public function setFieldsBuilder(FieldsBuilder $fieldsBuilder)
    {
        $this->fieldsBuilder = $fieldsBuilder;
    }

    /**
     * @param string $annotatedObjectClassName The FQCN of an object with a Type annotation.
     * @return MutableObjectType
     * @throws \ReflectionException
     */
    public function mapAnnotatedObject(string $annotatedObjectClassName): MutableObjectType
    {
        if ($this->recursiveTypeMapper === null) {
            throw new \RuntimeException('TypeGenerator::setRecursiveTypeMapper must be called while setting up GraphQLite.');
        }
        if ($this->fieldsBuilder === null) {
            throw new \RuntimeException('TypeGenerator::setFieldsBuilder must be called while setting up GraphQLite.');
        }

        $refTypeClass = new ReflectionClass($annotatedObjectClassName);

        $typeField = $this->annotationReader->getTypeAnnotation($refTypeClass);

        if ($typeField === null) {
            throw MissingAnnotationException::missingTypeException();
        }

        $typeName = $this->namingStrategy->getOutputTypeName($refTypeClass->getName(), $typeField);

        if ($this->typeRegistry->hasType($typeName)) {
            return $this->typeRegistry->getMutableObjectType($typeName);
        }

        if (!$typeField->isSelfType()) {
            $annotatedObject = $this->container->get($annotatedObjectClassName);
        } else {
            $annotatedObject = null;
        }

        return TypeAnnotatedObjectType::createFromAnnotatedClass($typeName, $typeField->getClass(), $annotatedObject, $this->fieldsBuilder, $this->recursiveTypeMapper);

        /*return new ObjectType([
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
        ]);*/
    }

    /**
     * @param object $annotatedObject An object with a ExtendType annotation.
     * @param MutableObjectType $type
     * @param RecursiveTypeMapperInterface $recursiveTypeMapper
     */
    public function extendAnnotatedObject($annotatedObject, MutableObjectType $type, RecursiveTypeMapperInterface $recursiveTypeMapper)
    {
        $refTypeClass = new ReflectionClass($annotatedObject);

        $extendTypeAnnotation = $this->annotationReader->getExtendTypeAnnotation($refTypeClass);

        if ($extendTypeAnnotation === null) {
            throw MissingAnnotationException::missingExtendTypeException();
        }

        //$typeName = $this->namingStrategy->getOutputTypeName($refTypeClass->getName(), $extendTypeAnnotation);
        $typeName = $type->name;

        /*if ($this->typeRegistry->hasType($typeName)) {
            throw new GraphQLException(sprintf('Tried to extend GraphQL type "%s" that is already stored in the type registry.', $typeName));
        }
        */

        $type->addFields(function() use ($annotatedObject) {
                /*$parentClass = get_parent_class($extendTypeAnnotation->getClass());
                $parentType = null;
                if ($parentClass !== false) {
                    if ($recursiveTypeMapper->canMapClassToType($parentClass)) {
                        $parentType = $recursiveTypeMapper->mapClassToType($parentClass, null);
                    }
                }*/

                return $this->fieldsBuilder->getFields($annotatedObject);
                /*if ($parentType !== null) {
                    $fields = $parentType->getFields() + $fields;
                }*/
            });
    }
}
