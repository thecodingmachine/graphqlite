<?php


namespace TheCodingMachine\GraphQL\Controllers;

use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\Type;
use TheCodingMachine\GraphQL\Controllers\Types\DateTimeType;

/**
 * A GraphQL field that maps to a PHP method automatically.
 */
class QueryField extends FieldDefinition
{
    /**
     * @var HydratorInterface
     */
    private $hydrator;

    /**
     * QueryField constructor.
     * @param string $name
     * @param OutputType&Type $type
     * @param array[] $arguments Indexed by argument name, value: ['type'=>InputType, 'default'=>val].
     * @param callable|null $resolve The method to execute
     * @param string|null $targetMethodOnSource The name of the method to execute on the source object. Mutually exclusive with $resolve parameter.
     * @param HydratorInterface $hydrator
     * @param null|string $comment
     * @param bool $injectSource Whether to inject the source object (for Fields), or null for Query and Mutations
     * @param array $additionalConfig
     */
    public function __construct(string $name, OutputType $type, array $arguments, ?callable $resolve, ?string $targetMethodOnSource, HydratorInterface $hydrator, ?string $comment, bool $injectSource, array $additionalConfig = [])
    {
        // FIXME: remove hydrator since not used!!!!
        $this->hydrator = $hydrator;
        $config = [
            'name' => $name,
            'type' => $type,
            'args' => array_map(function(array $item) { return $item['type']; }, $arguments)
        ];
        if ($comment) {
            $config['description'] = $comment;
        }

        $config['resolve'] = function ($source, array $args) use ($resolve, $targetMethodOnSource, $arguments, $injectSource) {
            $toPassArgs = [];
            if ($injectSource) {
                $toPassArgs[] = $source;
            }
            foreach ($arguments as $name => $arr) {
                $type = $arr['type'];
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
                            };
                            return $item;
                        }, $val);
                    } elseif ($type instanceof DateTimeType) {
                        $val = new \DateTimeImmutable($val);
                    } elseif ($type instanceof InputObjectType) {
                        $val = $this->hydrator->hydrate($val, $type);
                    }
                } elseif (array_key_exists('default', $arr)) {
                    $val = $arr['default'];
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
}
