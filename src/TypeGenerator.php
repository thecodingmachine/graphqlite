<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;
use TheCodingMachine\GraphQLite\Mappers\RecursiveTypeMapperInterface;
use TheCodingMachine\GraphQLite\Types\MutableInterface;
use TheCodingMachine\GraphQLite\Types\MutableInterfaceType;
use TheCodingMachine\GraphQLite\Types\MutableObjectType;
use TheCodingMachine\GraphQLite\Types\TypeAnnotatedInterfaceType;
use TheCodingMachine\GraphQLite\Types\TypeAnnotatedObjectType;
use function interface_exists;

/**
 * This class is in charge of creating Webonyx GraphQL types from annotated objects that do not extend the
 * Webonyx ObjectType class.
 */
class TypeGenerator
{
    /** @var AnnotationReader */
    private $annotationReader;
    /** @var FieldsBuilder */
    private $fieldsBuilder;
    /** @var NamingStrategyInterface */
    private $namingStrategy;
    /** @var TypeRegistry */
    private $typeRegistry;
    /** @var ContainerInterface */
    private $container;
    /** @var RecursiveTypeMapperInterface */
    private $recursiveTypeMapper;

    public function __construct(
        AnnotationReader $annotationReader,
        NamingStrategyInterface $namingStrategy,
        TypeRegistry $typeRegistry,
        ContainerInterface $container,
        RecursiveTypeMapperInterface $recursiveTypeMapper,
        FieldsBuilder $fieldsBuilder
    ) {
        $this->annotationReader    = $annotationReader;
        $this->namingStrategy      = $namingStrategy;
        $this->typeRegistry        = $typeRegistry;
        $this->container           = $container;
        $this->recursiveTypeMapper = $recursiveTypeMapper;
        $this->fieldsBuilder       = $fieldsBuilder;
    }

    /**
     * @param class-string<object> $annotatedObjectClassName The FQCN of an object with a Type annotation.
     *
     * @return MutableInterface&(MutableInterfaceType|MutableObjectType)
     *
     * @throws ReflectionException
     */
    public function mapAnnotatedObject(string $annotatedObjectClassName): MutableInterface
    {
        $refTypeClass = new ReflectionClass($annotatedObjectClassName);

        $typeField = $this->annotationReader->getTypeAnnotation($refTypeClass);

        if ($typeField === null) {
            throw MissingAnnotationException::missingTypeException($annotatedObjectClassName);
        }

        $typeName = $this->namingStrategy->getOutputTypeName($refTypeClass->getName(), $typeField);

        if ($this->typeRegistry->hasType($typeName)) {
            return $this->typeRegistry->getMutableInterface($typeName);
        }

        if (! $typeField->isSelfType()) {
            if (! $refTypeClass->isInstantiable()) {
                throw new GraphQLRuntimeException('Class "' . $annotatedObjectClassName . '" annotated with @Type(class="' . $typeField->getClass() . '") must be instantiable.');
            }
            $annotatedObject = $this->container->get($annotatedObjectClassName);
            $isInterface = interface_exists($typeField->getClass());
        } else {
            $annotatedObject = null;
            $isInterface = $refTypeClass->isInterface();
        }

        if ($isInterface) {
            return TypeAnnotatedInterfaceType::createFromAnnotatedClass($typeName, $typeField->getClass(), $annotatedObject, $this->fieldsBuilder, $this->recursiveTypeMapper);
        }

        return TypeAnnotatedObjectType::createFromAnnotatedClass($typeName, $typeField->getClass(), $annotatedObject, $this->fieldsBuilder, $this->recursiveTypeMapper, ! $typeField->isDefault());

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
     * @param MutableInterface&(MutableObjectType|MutableInterfaceType) $type
     *
     * @throws ReflectionException
     */
    public function extendAnnotatedObject(object $annotatedObject, MutableInterface $type): void
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

        $type->addFields(function () use ($annotatedObject) {
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
