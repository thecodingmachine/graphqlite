<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Types;

use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use InvalidArgumentException;
use ReflectionClass;
use TheCodingMachine\GraphQLite\FieldsBuilder;
use TheCodingMachine\GraphQLite\GraphQLRuntimeException;
use TheCodingMachine\GraphQLite\Mappers\RecursiveTypeMapperInterface;
use TheCodingMachine\GraphQLite\Reflection\ReflectionInterfaceUtils;
use function array_merge;
use function get_class;
use function gettype;
use function is_object;

/**
 * An object type built from the Type annotation
 */
class TypeAnnotatedInterfaceType extends MutableInterfaceType
{
    /** @var RecursiveTypeMapperInterface */
    private $recursiveTypeMapper;

    /**
     * @param mixed[] $config
     */
    public function __construct(string $className, array $config, RecursiveTypeMapperInterface $recursiveTypeMapper)
    {
        $this->recursiveTypeMapper = $recursiveTypeMapper;
        parent::__construct($config, $className);
    }

    public static function createFromAnnotatedClass(string $typeName, string $className, ?object $annotatedObject, FieldsBuilder $fieldsBuilder, RecursiveTypeMapperInterface $recursiveTypeMapper): self
    {
        return new self($className, [
            'name' => $typeName,
            'fields' => static function () use ($annotatedObject, $className, $fieldsBuilder) {
                // There is no need for an interface that extends another interface to get all its fields.
                // Indeed, if the interface is used, the extended interfaces will be used too. Therefore, fetching the fields
                // and putting them in the child interface is a waste of resources.
                /*$interfaces = ReflectionInterfaceUtils::getDirectlyImplementedInterfaces(new ReflectionClass($className));

                $fieldsArray = [];
                foreach ($interfaces as $interfaceName => $interface) {
                    if (! $recursiveTypeMapper->canMapClassToType($interfaceName)) {
                        continue;
                    }

                    $interfaceType = $recursiveTypeMapper->mapClassToType($interfaceName, null);
                    $fieldsArray[] = $interfaceType->getFields();
                }

                if (! empty($fieldsArray)) {
                    $interfaceFields = array_merge(...$fieldsArray);
                } else {
                    $interfaceFields = [];
                }*/

                if ($annotatedObject !== null) {
                    $fields = $fieldsBuilder->getFields($annotatedObject);
                } else {
                    $fields = $fieldsBuilder->getSelfFields($className);
                }

                //$fields += $interfaceFields;

                return $fields;
            }
        ], $recursiveTypeMapper);
    }

    /**
     * Resolves concrete ObjectType for given object value
     *
     * @param mixed[] $context
     */
    public function resolveType($objectValue, $context, ResolveInfo $info)
    {
        if (! is_object($objectValue)) {
            throw new InvalidArgumentException('Expected object for resolveType. Got: "' . gettype($objectValue) . '"');
        }

        $className = get_class($objectValue);

        if ($this->recursiveTypeMapper->canMapClassToType($className)) {
            return $this->recursiveTypeMapper->mapClassToType($className, null);
        }

        return $this->recursiveTypeMapper->getGeneratedObjectTypeFromInterfaceType($this);
    }
}
