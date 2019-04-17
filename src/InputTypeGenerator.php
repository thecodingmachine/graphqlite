<?php


namespace TheCodingMachine\GraphQLite;

use function get_parent_class;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ObjectType;
use phpDocumentor\Reflection\Fqsen;
use phpDocumentor\Reflection\Types\Object_;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionMethod;
use ReflectionType;
use TheCodingMachine\GraphQLite\Annotations\Type;
use TheCodingMachine\GraphQLite\Hydrators\HydratorInterface;
use TheCodingMachine\GraphQLite\Mappers\RecursiveTypeMapperInterface;
use TheCodingMachine\GraphQLite\Types\ArgumentResolver;
use TheCodingMachine\GraphQLite\Types\ResolvableInputObjectType;

/**
 * This class is in charge of creating Webonyx InputTypes from Factory annotations.
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
     * @var ArgumentResolver
     */
    private $argumentResolver;
    /**
     * @var InputTypeUtils
     */
    private $inputTypeUtils;

    public function __construct(InputTypeUtils $inputTypeUtils,
                                FieldsBuilderFactory $fieldsBuilderFactory,
                                ArgumentResolver $argumentResolver)
    {
        $this->inputTypeUtils = $inputTypeUtils;
        $this->fieldsBuilderFactory = $fieldsBuilderFactory;
        $this->argumentResolver = $argumentResolver;
    }

    /**
     * @param string $factory
     * @param string $methodName
     * @param RecursiveTypeMapperInterface $recursiveTypeMapper
     * @return InputObjectType
     */
    public function mapFactoryMethod(string $factory, string $methodName, RecursiveTypeMapperInterface $recursiveTypeMapper, ContainerInterface $container): InputObjectType
    {
        $method = new ReflectionMethod($factory, $methodName);

        if ($method->isStatic()) {
            $object = $factory;
        } else {
            $object = $container->get($factory);
        }

        [$inputName, $className] = $this->inputTypeUtils->getInputTypeNameAndClassName($method);

        if (!isset($this->cache[$inputName])) {
            // TODO: add comment argument.
            $this->cache[$inputName] = new ResolvableInputObjectType($inputName, $this->fieldsBuilderFactory, $recursiveTypeMapper, $object, $methodName, $this->argumentResolver, null);
        }

        return $this->cache[$inputName];
    }
}
