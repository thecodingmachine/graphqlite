<?php


namespace TheCodingMachine\GraphQLite\Types;

use function get_class;
use GraphQL\Type\Definition\InputObjectType;
use ReflectionMethod;
use TheCodingMachine\GraphQLite\FieldsBuilder;
use TheCodingMachine\GraphQLite\GraphQLException;

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
     * @param FieldsBuilder $fieldsBuilder
     * @param object|string $factory
     * @param string $methodName
     * @param ArgumentResolver $argumentResolver
     * @param null|string $comment
     * @param array $additionalConfig
     */
    public function __construct(string $name, FieldsBuilder $fieldsBuilder, $factory, string $methodName, ArgumentResolver $argumentResolver, ?string $comment, array $additionalConfig = [])
    {
        $this->argumentResolver = $argumentResolver;
        $this->resolve = [ $factory, $methodName ];

        $fields = function() use ($fieldsBuilder, $factory, $methodName) {
            $method = new ReflectionMethod($factory, $methodName);
            return $fieldsBuilder->getInputFields($method);
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
