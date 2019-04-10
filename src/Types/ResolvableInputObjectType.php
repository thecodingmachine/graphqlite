<?php


namespace TheCodingMachine\GraphQLite\Types;

use function get_class;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\Type;
use ReflectionMethod;
use TheCodingMachine\GraphQLite\FieldsBuilderFactory;
use TheCodingMachine\GraphQLite\GraphQLException;
use TheCodingMachine\GraphQLite\Hydrators\HydratorInterface;
use TheCodingMachine\GraphQLite\Mappers\RecursiveTypeMapperInterface;
use TheCodingMachine\GraphQLite\Types\DateTimeType;

/**
 * A GraphQL input object that can be resolved using a factory
 */
class ResolvableInputObjectType extends InputObjectType implements ResolvableInputInterface
{
    /**
     * @var ArgumentResolver
     */
    private $argumentResolver;

    /**
     * @var callable&array<int, object|string>
     */
    private $resolve;

    /**
     * QueryField constructor.
     * @param string $name
     * @param FieldsBuilderFactory $controllerQueryProviderFactory
     * @param RecursiveTypeMapperInterface $recursiveTypeMapper
     * @param object|string $factory
     * @param string $methodName
     * @param ArgumentResolver $argumentResolver
     * @param null|string $comment
     * @param array $additionalConfig
     */
    public function __construct(string $name, FieldsBuilderFactory $controllerQueryProviderFactory, RecursiveTypeMapperInterface $recursiveTypeMapper, $factory, string $methodName, ArgumentResolver $argumentResolver, ?string $comment, array $additionalConfig = [])
    {
        $this->argumentResolver = $argumentResolver;
        $this->resolve = [ $factory, $methodName ];

        $fields = function() use ($controllerQueryProviderFactory, $factory, $methodName, $recursiveTypeMapper) {
            $method = new ReflectionMethod($factory, $methodName);
            $fieldProvider = $controllerQueryProviderFactory->buildFieldsBuilder($recursiveTypeMapper);
            return $fieldProvider->getInputFields($method);
        };

        $config = [
            'name' => $name,
            'fields' => $fields,
        ];
        if ($comment) {
            $config['description'] = $comment;
        }

        $config += $additionalConfig;
        parent::__construct($config);
    }

    /**
     * @param array $args
     * @return object
     */
    public function resolve(array $args)
    {
        $toPassArgs = [];
        foreach ($this->getFields() as $name => $field) {
            $type = $field->getType();
            if (isset($args[$name])) {
                $val = $this->argumentResolver->resolve($args[$name], $type);
            } elseif ($field->defaultValueExists()) {
                $val = $field->defaultValue;
            } else {
                throw new GraphQLException("Expected argument '$name' was not provided in GraphQL input type '".$this->name."' used in factory '".get_class($this->resolve[0]).'::'.$this->resolve[1]."()'");
            }

            $toPassArgs[] = $val;
        }

        $resolve = $this->resolve;

        return $resolve(...$toPassArgs);
    }
}
