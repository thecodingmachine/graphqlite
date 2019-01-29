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
     * @var HydratorInterface
     */
    private $hydrator;

    /**
     * @var callable&array<int, object|string>
     */
    private $resolve;

    /**
     * QueryField constructor.
     * @param string $name
     * @param FieldsBuilderFactory $controllerQueryProviderFactory
     * @param RecursiveTypeMapperInterface $recursiveTypeMapper
     * @param object $factory
     * @param string $methodName
     * @param HydratorInterface $hydrator
     * @param null|string $comment
     * @param array $additionalConfig
     */
    public function __construct(string $name, FieldsBuilderFactory $controllerQueryProviderFactory, RecursiveTypeMapperInterface $recursiveTypeMapper, $factory, string $methodName, HydratorInterface $hydrator, ?string $comment, array $additionalConfig = [])
    {
        $this->hydrator = $hydrator;
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
                $val = $args[$name];

                $type = $this->stripNonNullType($type);
                if ($type instanceof ListOfType) {
                    $subtype = $this->stripNonNullType($type->getWrappedType());
                    $val = array_map(function ($item) use ($subtype) {
                        if ($subtype instanceof DateTimeType) {
                            return new \DateTimeImmutable($item);
                        } elseif ($subtype instanceof InputObjectType) {
                            return $this->hydrator->hydrate($item, $subtype);
                        }
                        return $item;
                    }, $val);
                } elseif ($type instanceof DateTimeType) {
                    $val = new \DateTimeImmutable($val);
                } elseif ($type instanceof InputObjectType) {
                    $val = $this->hydrator->hydrate($val, $type);
                }
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
    
    private function stripNonNullType(Type $type): Type
    {
        if ($type instanceof NonNull) {
            return $this->stripNonNullType($type->getWrappedType());
        }
        return $type;
    }
}
