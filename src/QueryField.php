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
     * @param TypeInterface[] $arguments Indexed by argument name.
     * @param callable $resolve
     * @param array $additionalConfig
     */
    public function __construct(string $name, TypeInterface $type, array $arguments, callable $resolve, HydratorInterface $hydrator, array $additionalConfig = [])
    {
        $this->hydrator = $hydrator;
        $config = [
            'name' => $name,
            'type' => $type,
            'args' => array_map(function(array $item) { return $item['type']; }, $arguments)
        ];

        $config['resolve'] = function ($source, array $args, ResolveInfo $info) use ($resolve, $arguments) {
            $toPassArgs = [];
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
                } elseif (isset($arr['default'])) {
                    $val = $arr['default'];
                } else {
                    throw new GraphQLException("Expected argument '$name' was not provided.");
                }

                $toPassArgs[] = $val;
            }

            return $resolve(...$toPassArgs);
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
