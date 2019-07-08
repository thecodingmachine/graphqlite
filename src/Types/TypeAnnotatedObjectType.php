<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Types;

use TheCodingMachine\GraphQLite\FieldsBuilder;
use TheCodingMachine\GraphQLite\Mappers\RecursiveTypeMapperInterface;
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

    public static function createFromAnnotatedClass(string $typeName, string $className, ?object $annotatedObject, FieldsBuilder $fieldsBuilder, RecursiveTypeMapperInterface $recursiveTypeMapper, bool $doNotMapInterfaces, bool $disableInheritance): self
    {
        return new self($className, [
            'name' => $typeName,
            'fields' => static function () use ($annotatedObject, $recursiveTypeMapper, $className, $fieldsBuilder, $disableInheritance) {
                $parentClass = get_parent_class($className);
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
                }

                return $fields;
            },
            'interfaces' => static function () use ($className, $recursiveTypeMapper, $doNotMapInterfaces, $disableInheritance) {
                if ($doNotMapInterfaces === true || $disableInheritance === true) {
                    return [];
                }

                return $recursiveTypeMapper->findInterfaces($className);
            },
        ]);
    }
}
