<?php


namespace TheCodingMachine\GraphQLite;

use function array_filter;
use function array_map;
use function array_unshift;
use function array_values;
use function get_class;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\IDType;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\LeafType;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Type\Definition\Type;
use InvalidArgumentException;
use function is_array;
use TheCodingMachine\GraphQLite\Hydrators\HydratorInterface;
use TheCodingMachine\GraphQLite\Parameters\InputTypeParameter;
use TheCodingMachine\GraphQLite\Parameters\ParameterInterface;
use TheCodingMachine\GraphQLite\Parameters\SourceParameter;
use TheCodingMachine\GraphQLite\Types\ArgumentResolver;
use TheCodingMachine\GraphQLite\Types\DateTimeType;
use TheCodingMachine\GraphQLite\Types\ID;

/**
 * A GraphQL field that maps to a PHP method automatically.
 *
 * @internal
 */
class QueryField extends FieldDefinition
{
    /**
     * QueryField constructor.
     * @param string $name
     * @param OutputType&Type $type
     * @param array<string, ParameterInterface> $arguments Indexed by argument name.
     * @param callable|null $resolve The method to execute
     * @param string|null $targetMethodOnSource The name of the method to execute on the source object. Mutually exclusive with $resolve parameter.
     * @param null|string $comment
     * @param array $additionalConfig
     */
    public function __construct(string $name, OutputType $type, array $arguments, ?callable $resolve, ?string $targetMethodOnSource, ?string $comment, array $additionalConfig = [])
    {
        $config = [
            'name' => $name,
            'type' => $type,
            'args' => InputTypeUtils::getInputTypeArgs($arguments)
        ];
        if ($comment) {
            $config['description'] = $comment;
        }

        $config['resolve'] = function ($source, array $args, $context, ResolveInfo $info) use ($resolve, $targetMethodOnSource, $arguments) {
            $toPassArgs = array_values(array_map(function(ParameterInterface $parameter) use ($source, $args, $context, $info) {
                return $parameter->resolve($source, $args, $context, $info);
            }, $arguments));

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
     * @param mixed $value A value that will always be returned by this field.
     * @return QueryField
     */
    public static function alwaysReturn(string $name, OutputType $type, array $arguments, $value, ?string $comment): self
    {
        if ($value === null && $type instanceof NonNull) {
            $type = $type->getWrappedType();
        }
        $callable = function() use ($value) {
            return $value;
        };
        return new self($name, $type, $arguments, $callable, null, $comment);
    }

    public static function selfField(string $name, OutputType $type, array $arguments, string $targetMethodOnSource, ?string $comment): self
    {
        return new self($name, $type, $arguments, null, $targetMethodOnSource, $comment);
    }

    public static function externalField(string $name, OutputType $type, array $arguments, $callable, ?string $comment, bool $injectSource): self
    {
        if ($injectSource === true) {
            array_unshift($arguments, new SourceParameter());
        }
        return new self($name, $type, $arguments, $callable, null, $comment);
    }
}
