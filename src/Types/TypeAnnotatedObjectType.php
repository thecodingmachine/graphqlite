<?php


namespace TheCodingMachine\GraphQL\Controllers\Types;

use GraphQL\Type\Definition\ObjectType;
use TheCodingMachine\GraphQL\Controllers\Annotations\Type;
use TheCodingMachine\GraphQL\Controllers\FieldsBuilderFactory;
use TheCodingMachine\GraphQL\Controllers\Mappers\RecursiveTypeMapperInterface;

/**
 * An object type built from the Type annotation
 */
class TypeAnnotatedObjectType extends MutableObjectType
{
    /**
     * @var string
     */
    private $className;

    public function __construct(string $className, array $config)
    {
        $this->className = $className;

        parent::__construct($config);
    }

    public static function createFromAnnotatedClass(string $typeName, string $className, $annotatedObject, FieldsBuilderFactory $fieldsBuilderFactory, RecursiveTypeMapperInterface $recursiveTypeMapper): self
    {
        return new self($className, [
            'name' => $typeName,
            'fields' => function() use ($annotatedObject, $recursiveTypeMapper, $className, $fieldsBuilderFactory) {
                $parentClass = get_parent_class($className);
                $parentType = null;
                if ($parentClass !== false) {
                    if ($recursiveTypeMapper->canMapClassToType($parentClass)) {
                        $parentType = $recursiveTypeMapper->mapClassToType($parentClass, null);
                    }
                }

                $fieldProvider = $fieldsBuilderFactory->buildFieldsBuilder($recursiveTypeMapper);
                $fields = $fieldProvider->getFields($annotatedObject);
                if ($parentType !== null) {
                    $fields = $parentType->getFields() + $fields;
                }
                return $fields;
            },
            'interfaces' => function() use ($className, $recursiveTypeMapper) {
                return $recursiveTypeMapper->findInterfaces($className);
            }
        ]);
    }

    public function getMappedClassName(): string
    {
        return $this->className;
    }
}
