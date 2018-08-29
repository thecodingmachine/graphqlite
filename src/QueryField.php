<?php


namespace TheCodingMachine\GraphQL\Controllers;

use TheCodingMachine\GraphQL\Controllers\Registry\Registry;
use Youshido\GraphQL\Execution\ResolveInfo;
use Youshido\GraphQL\Field\AbstractField;
use Youshido\GraphQL\Type\AbstractType;
use Youshido\GraphQL\Type\ListType\ListType;
use Youshido\GraphQL\Type\NonNullType;
use Youshido\GraphQL\Type\Object\AbstractObjectType;
use Youshido\GraphQL\Type\Scalar\DateTimeType;
use Youshido\GraphQL\Type\TypeInterface;
use Youshido\GraphQL\Type\TypeMap;

/**
 * A GraphQL field that maps to a PHP method automatically.
 */
class QueryField extends AbstractField
{
    /**
     * @var HydratorInterface
     */
    private $hydrator;

    /**
     * QueryField constructor.
     * @param string $name
     * @param TypeInterface $type
     * @param array[] $arguments Indexed by argument name, value: ['type'=>TypeInterface, 'default'=>val].
     * @param callable|null $resolve The method to execute
     * @param string|null $targetMethodOnSource The name of the method to execute on the source object. Mutually exclusive with $resolve parameter.
     * @param HydratorInterface $hydrator
     * @param null|string $comment
     * @param bool $injectSource Whether to inject the source object (for Fields), or null for Query and Mutations
     * @param array $additionalConfig
     */
    public function __construct(string $name, TypeInterface $type, array $arguments, ?callable $resolve, ?string $targetMethodOnSource, HydratorInterface $hydrator, ?string $comment, bool $injectSource, array $additionalConfig = [])
    {
        $this->hydrator = $hydrator;
        $config = [
            'name' => $name,
            'type' => $type,
            'args' => array_map(function(array $item) { return $item['type']; }, $arguments)
        ];
        if ($comment) {
            $config['description'] = $comment;
        }

        $config['resolve'] = function ($source, array $args, ResolveInfo $info) use ($resolve, $targetMethodOnSource, $arguments, $injectSource) {
            $toPassArgs = [];
            if ($injectSource) {
                $toPassArgs[] = $source;
            }
            foreach ($arguments as $name => $arr) {
                $type = $arr['type'];
                if (isset($args[$name])) {
                    $val = $args[$name];

                    $type = $this->stripNonNullType($type);
                    if ($type instanceof ListType) {
                        $subtype = $this->stripNonNullType($type->getItemType());
                        $val = array_map(function ($item) use ($subtype) {
                            if ($subtype instanceof DateTimeType) {
                                return new \DateTimeImmutable($item);
                            } elseif ($subtype->getKind() === TypeMap::KIND_INPUT_OBJECT) {
                                return $this->hydrator->hydrate($item, $subtype);
                            };
                            return $item;
                        }, $val);
                    } elseif ($type instanceof DateTimeType) {
                        $val = new \DateTimeImmutable($val);
                    } elseif ($type->getKind() === TypeMap::KIND_INPUT_OBJECT) {
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

    /**
     * @return AbstractObjectType|AbstractType
     */
    public function getType()
    {
        return $this->config->getType();
    }

    private function stripNonNullType(TypeInterface $type): TypeInterface
    {
        if ($type instanceof NonNullType) {
            return $this->stripNonNullType($type->getTypeOf());
        }
        return $type;
    }
}
