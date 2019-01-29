<?php


namespace TheCodingMachine\GraphQLite;

use function get_class;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\IDType;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Type\Definition\Type;
use InvalidArgumentException;
use function is_array;
use TheCodingMachine\GraphQLite\Hydrators\HydratorInterface;
use TheCodingMachine\GraphQLite\Types\DateTimeType;
use TheCodingMachine\GraphQLite\Types\ID;

/**
 * A GraphQL field that maps to a PHP method automatically.
 */
class QueryField extends FieldDefinition
{
    /**
     * QueryField constructor.
     * @param string $name
     * @param OutputType&Type $type
     * @param array[] $arguments Indexed by argument name, value: ['type'=>InputType, 'defaultValue'=>val].
     * @param callable|null $resolve The method to execute
     * @param string|null $targetMethodOnSource The name of the method to execute on the source object. Mutually exclusive with $resolve parameter.
     * @param HydratorInterface $hydrator
     * @param null|string $comment
     * @param bool $injectSource Whether to inject the source object (for Fields), or null for Query and Mutations
     * @param array $additionalConfig
     */
    public function __construct(string $name, OutputType $type, array $arguments, ?callable $resolve, ?string $targetMethodOnSource, HydratorInterface $hydrator, ?string $comment, bool $injectSource, array $additionalConfig = [])
    {
        $config = [
            'name' => $name,
            'type' => $type,
            'args' => array_map(function(array $item) { return $item['type']; }, $arguments)
        ];
        if ($comment) {
            $config['description'] = $comment;
        }

        $config['resolve'] = function ($source, array $args) use ($resolve, $targetMethodOnSource, $arguments, $injectSource, $hydrator) {
            $toPassArgs = [];
            if ($injectSource) {
                $toPassArgs[] = $source;
            }
            foreach ($arguments as $name => $arr) {
                $type = $arr['type'];
                if (isset($args[$name])) {
                    $val = $this->castVal($args[$name], $type, $hydrator);
                } elseif (array_key_exists('defaultValue', $arr)) {
                    $val = $arr['defaultValue'];
                } else {
                    throw new GraphQLException("Expected argument '$name' was not provided.");
                }

                $toPassArgs[] = $val;
            }

            if ($resolve !== null) {
                return $resolve(...$toPassArgs);
            }
            if ($targetMethodOnSource !== null) {
                $method = [$source, $targetMethodOnSource];
                return $method(...$toPassArgs);
            }
            throw new \InvalidArgumentException('The QueryField constructor should be passed either a resolve method or a target method on source object.');
        };

        $config += $additionalConfig;
        parent::__construct($config);
    }

    private function stripNonNullType(Type $type): Type
    {
        if ($type instanceof NonNull) {
            return $this->stripNonNullType($type->getWrappedType());
        }
        return $type;
    }

    /**
     * Casts a value received from GraphQL into an argument passed to a method.
     *
     * @param mixed $val
     * @param InputType $type
     * @return mixed
     */
    private function castVal($val, InputType $type, HydratorInterface $hydrator)
    {
        $type = $this->stripNonNullType($type);
        if ($type instanceof ListOfType) {
            if (!is_array($val)) {
                throw new InvalidArgumentException('Expected GraphQL List but value passed is not an array.');
            }
            return array_map(function($item) use ($type, $hydrator) {
                return $this->castVal($item, $type->getWrappedType(), $hydrator);
            }, $val);
        } elseif ($type instanceof DateTimeType) {
            return new \DateTimeImmutable($val);
        } elseif ($type instanceof IDType) {
            return new ID($val);
        } elseif ($type instanceof InputObjectType) {
            return $hydrator->hydrate($val, $type);
        } elseif (!$type instanceof ScalarType) {
            throw new \RuntimeException('Unexpected type: '.get_class($type));
        }
        return $val;
    }
}
