<?php


namespace TheCodingMachine\GraphQLite;

use function get_parent_class;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ObjectType;
use phpDocumentor\Reflection\Fqsen;
use phpDocumentor\Reflection\Types\Object_;
use ReflectionClass;
use ReflectionMethod;
use ReflectionType;
use TheCodingMachine\GraphQLite\Annotations\Type;
use TheCodingMachine\GraphQLite\Hydrators\HydratorInterface;
use TheCodingMachine\GraphQLite\Mappers\RecursiveTypeMapperInterface;
use TheCodingMachine\GraphQLite\Types\ResolvableInputObjectType;

/**
 * This class is in charge of creating Webonix InputTypes from Factory annotations.
 */
class InputTypeGenerator
{
    /**
     * @var FieldsBuilderFactory
     */
    private $fieldsBuilderFactory;
    /**
     * @var array<string, InputObjectType>
     */
    private $cache = [];
    /**
     * @var HydratorInterface
     */
    private $hydrator;
    /**
     * @var InputTypeUtils
     */
    private $inputTypeUtils;

    public function __construct(InputTypeUtils $inputTypeUtils,
                                FieldsBuilderFactory $fieldsBuilderFactory,
                                HydratorInterface $hydrator)
    {
        $this->inputTypeUtils = $inputTypeUtils;
        $this->fieldsBuilderFactory = $fieldsBuilderFactory;
        $this->hydrator = $hydrator;
    }

    /**
     * @param object $factory
     * @param string $methodName
     * @param RecursiveTypeMapperInterface $recursiveTypeMapper
     * @return InputObjectType
     */
    public function mapFactoryMethod($factory, string $methodName, RecursiveTypeMapperInterface $recursiveTypeMapper): InputObjectType
    {
        $method = new ReflectionMethod($factory, $methodName);

        [$inputName, $className] = $this->inputTypeUtils->getInputTypeNameAndClassName($method);

        if (!isset($this->cache[$inputName])) {
            // TODO: add comment argument.
            $this->cache[$inputName] = new ResolvableInputObjectType($inputName, $this->fieldsBuilderFactory, $recursiveTypeMapper, $factory, $methodName, $this->hydrator, null);
        }

        return $this->cache[$inputName];
    }
}
