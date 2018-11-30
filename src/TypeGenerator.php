<?php


namespace TheCodingMachine\GraphQL\Controllers;

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

    public function __construct(AnnotationReader $annotationReader,
                                ControllerQueryProviderFactory $controllerQueryProviderFactory)
    {
        $this->annotationReader = $annotationReader;
        $this->controllerQueryProviderFactory = $controllerQueryProviderFactory;
    }

    /**
     * @param object $annotatedObject An object with a @Type annotation.
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

        $type = new ObjectType([
            'name' => $this->getName($refTypeClass, $typeField),
            'fields' => function() use ($annotatedObject, $recursiveTypeMapper) {
                $fieldProvider = $this->controllerQueryProviderFactory->buildQueryProvider($annotatedObject, $recursiveTypeMapper);
                return $fieldProvider->getFields();
            },
            'interfaces' => function() use ($typeField, $recursiveTypeMapper) {
                return $recursiveTypeMapper->findInterfaces($typeField->getClass());
            }
        ]);

        return $type;
    }

    private function getName(ReflectionClass $refTypeClass, Type $type): string
    {
        $className = $refTypeClass->getName();

        if ($prevPos = strrpos($className, '\\')) {
            $className = substr($className, $prevPos + 1);
        }
        // By default, if the class name ends with Type, let's take the name of the class for the type
        if (substr($className, -4) === 'Type') {
            return substr($className, 0, -4);
        }
        // Else, let's take the name of the targeted class
        $className = $type->getClass();
        if ($prevPos = strrpos($className, '\\')) {
            $className = substr($className, $prevPos + 1);
        }
        return $className;
    }
}
