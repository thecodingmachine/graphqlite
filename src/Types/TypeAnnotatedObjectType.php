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
    /** @var string */
    private $className;

    /**
     * @param mixed[] $config
     */
    public function __construct(string $className, array $config)
    {
        $this->className = $className;

        parent::__construct($config);
    }

    public static function createFromAnnotatedClass(string $typeName, string $className, ?object $annotatedObject, FieldsBuilder $fieldsBuilder, RecursiveTypeMapperInterface $recursiveTypeMapper) : self
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

                    return $finalFields;
                }

                return $fields;
            },
            'interfaces' => static function () use ($className, $recursiveTypeMapper) {
                return $recursiveTypeMapper->findInterfaces($className);
            },
        ]);
    }

    public function getMappedClassName() : string
    {
        return $this->className;
    }
}
