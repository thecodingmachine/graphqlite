<?php


namespace TheCodingMachine\GraphQL\Controllers;

use GraphQL\Type\Definition\ObjectType;
use ReflectionClass;
use TheCodingMachine\GraphQL\Controllers\Annotations\Exceptions\ClassNotFoundException;
use TheCodingMachine\GraphQL\Controllers\Annotations\Type;
use TheCodingMachine\GraphQL\Controllers\Registry\RegistryInterface;

/**
 * This class is in charge of creating Webonix GraphQL types from annotated objects that do not extend the
 * Webonix ObjectType class.
 */
class TypeGenerator
{
    /**
     * @var RegistryInterface
     */
    private $registry;

    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @param object $annotatedObject An object with a @Type annotation.
     * @return ObjectType
     */
    public function mapAnnotatedObject($annotatedObject): ObjectType
    {
        $refTypeClass = new \ReflectionClass($annotatedObject);

        $typeField = $this->registry->getAnnotationReader()->getTypeAnnotation($refTypeClass);

        if ($typeField === null) {
            throw MissingAnnotationException::missingTypeException();
        }

        $type = new ObjectType([
            'name' => $this->getName($refTypeClass, $typeField),
            'fields' => function() use ($annotatedObject) {
                $fieldProvider = new ControllerQueryProvider($annotatedObject, $this->registry);
                return $fieldProvider->getFields();
            },
            'interfaces' => function() {
                // TODO: we need to ask: WHAT INTERFACES do we implement????
                // We need to be able to ask, given a type: what parent do we have???
                // TODO: from Type => go to class => recursively go to parent class => search for Type

                // Simple idea: every type has an interface attached.


                return [];
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
