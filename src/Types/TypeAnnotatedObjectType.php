<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Types;

use TheCodingMachine\GraphQLite\FieldsBuilder;
use TheCodingMachine\GraphQLite\Mappers\RecursiveTypeMapperInterface;
use function class_implements;
use function get_parent_class;

/**
 * An object type built from the Type annotation
 */
class TypeAnnotatedObjectType extends MutableObjectType
{
    /**
     * @param mixed[] $config
     */
    public function __construct(string $className, array $config)
    {
        parent::__construct($config, $className);
    }

    public static function createFromAnnotatedClass(string $typeName, string $className, ?object $annotatedObject, FieldsBuilder $fieldsBuilder, RecursiveTypeMapperInterface $recursiveTypeMapper, bool $doNotMapInterfaces): self
    {
        return new self($className, [
            'name' => $typeName,
            'fields' => static function () use ($annotatedObject, $recursiveTypeMapper, $className, $fieldsBuilder) {
                $parentClass = get_parent_class($className);
                $parentType  = null;
                if ($parentClass !== false) {
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

                    $fields = $finalFields;
                }

                // FIXME: we must get interfaces in THE CORRECT ORDER!!!!
                // FIXME: write tests for the order!!! => with 2 @ExtendType on the interface

                // FIXME: add an interface with a @Type that is implemented by noone.
                // Check that it does not trigger an exception.
                $interfaces = class_implements($className);
                foreach ($interfaces as $interface) {
                    if (! $recursiveTypeMapper->canMapClassToType($interface)) {
                        continue;
                    }

                    $interfaceType = $recursiveTypeMapper->mapClassToType($interface, null);

                    $interfaceFields = $interfaceType->getFields();
                    foreach ($interfaceFields as $name => $field) {
                        if (isset($fields[$name])) {
                            continue;
                        }

                        $fields[$name] = $field;
                    }
                }

                return $fields;
            },
            'interfaces' => static function () use ($className, $recursiveTypeMapper, $doNotMapInterfaces) {
                if ($doNotMapInterfaces === true) {
                    return [];
                }

                return $recursiveTypeMapper->findInterfaces($className);
            },
        ]);
    }
}
