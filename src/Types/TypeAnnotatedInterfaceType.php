<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Types;

use function get_class;
use InvalidArgumentException;
use function is_object;
use TheCodingMachine\GraphQLite\FieldsBuilder;
use TheCodingMachine\GraphQLite\Mappers\RecursiveTypeMapperInterface;
use function get_parent_class;

/**
 * An object type built from the Type annotation
 */
class TypeAnnotatedInterfaceType extends MutableInterfaceType
{
    /**
     * @param mixed[] $config
     */
    public function __construct(string $className, array $config)
    {
        parent::__construct($config, $className);
    }

    public static function createFromAnnotatedClass(string $typeName, string $className, ?object $annotatedObject, FieldsBuilder $fieldsBuilder, RecursiveTypeMapperInterface $recursiveTypeMapper, bool $doNotMapInterfaces, bool $disableInheritance): self
    {
        return new self($className, [
            'name' => $typeName,
            'fields' => static function () use ($annotatedObject, $recursiveTypeMapper, $className, $fieldsBuilder, $disableInheritance) {

                // TODO: get fields from parent interfaces
                // TODO: get fields from parent interfaces
                // TODO: get fields from parent interfaces
                // TODO: get fields from parent interfaces
                // TODO: get fields from parent interfaces
                // TODO: get fields from parent interfaces
                // TODO: get fields from parent interfaces
                // TODO: get fields from parent interfaces
                // QUESTION: DO WE NEED ANOTHER SET OF FUNCTIONS IN THE TYPEMAPPER OR DO WE USE THE CURRENT getByClassName...
                // QUESTION: DO WE NEED ANOTHER SET OF FUNCTIONS IN THE TYPEMAPPER OR DO WE USE THE CURRENT getByClassName...
                // QUESTION: DO WE NEED ANOTHER SET OF FUNCTIONS IN THE TYPEMAPPER OR DO WE USE THE CURRENT getByClassName... ???

                /*$parentClass = get_parent_class($className);
                $parentType  = null;
                if ($parentClass !== false && $disableInheritance === false) {
                    if ($recursiveTypeMapper->canMapClassToType($parentClass)) {
                        $parentType = $recursiveTypeMapper->mapClassToType($parentClass, null);
                    }
                }

                if ($annotatedObject !== null) {
                    $fields = $fieldsBuilder->getFields($annotatedObject);
                } else {
                    $fields = $fieldsBuilder->getSelfFields($className);
                }
                if ($parentType !== null) {
                    $finalFields = $parentType->getFields();
                    foreach ($fields as $name => $field) {
                        $finalFields[$name] = $field;
                    }

                    return $finalFields;
                }*/

                return $fields;
            },
            'resolve' => static function ($value) use ($recursiveTypeMapper) {
                if (! is_object($value)) {
                    throw new InvalidArgumentException('Expected object for resolveType. Got: "' . gettype($value) . '"');
                }

                $className = get_class($value);

                return $recursiveTypeMapper->mapClassToType($className, null);
            },
        ]);
    }
}
