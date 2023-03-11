<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Types;

use GraphQL\Type\Definition\ObjectType;
use TheCodingMachine\GraphQLite\FieldsBuilder;
use TheCodingMachine\GraphQLite\Mappers\RecursiveTypeMapperInterface;

use function class_implements;
use function get_parent_class;

/**
 * An object type built from the Type annotation.
 *
 * @phpstan-import-type ObjectConfig from ObjectType
 */
class TypeAnnotatedObjectType extends MutableObjectType
{
    /**
     * @param class-string<object> $className
     * @param ObjectConfig         $config
     */
    public function __construct(string $className, array $config)
    {
        parent::__construct($config, $className);
    }

    /** @param class-string<object> $className */
    public static function createFromAnnotatedClass(string $typeName, string $className, object|null $annotatedObject, FieldsBuilder $fieldsBuilder, RecursiveTypeMapperInterface $recursiveTypeMapper, bool $doNotMapInterfaces): self
    {
        return new self($className, [
            'name' => $typeName,
            'fields' => static function () use ($annotatedObject, $recursiveTypeMapper, $className, $fieldsBuilder, $typeName) {
                $parentClass = get_parent_class($className);
                $parentType = null;
                if ($parentClass !== false) {
                    if ($recursiveTypeMapper->canMapClassToType($parentClass)) {
                        $parentType = $recursiveTypeMapper->mapClassToType($parentClass, null);
                    }
                }

                if ($annotatedObject !== null) {
                    $fields = $fieldsBuilder->getFields($annotatedObject, $typeName);
                } else {
                    $fields = $fieldsBuilder->getSelfFields($className, $typeName);
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

                /** @var array<int, class-string<object>> $interfaces */
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
