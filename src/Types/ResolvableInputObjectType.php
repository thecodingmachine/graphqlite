<?php


namespace TheCodingMachine\GraphQLite\Types;

use function get_class;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use function is_object;
use ReflectionMethod;
use TheCodingMachine\GraphQLite\FieldsBuilder;
use TheCodingMachine\GraphQLite\GraphQLException;
use TheCodingMachine\GraphQLite\InputTypeUtils;
use TheCodingMachine\GraphQLite\Parameters\MissingArgumentException;
use TheCodingMachine\GraphQLite\Parameters\ParameterInterface;
use TheCodingMachine\GraphQLite\QueryField;

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
     * @var ParameterInterface[]
     */
    private $parameters;
    /**
     * @var FieldsBuilder
     */
    private $fieldsBuilder;

    /**
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
        $this->fieldsBuilder = $fieldsBuilder;

        $fields = function() {
            return InputTypeUtils::getInputTypeArgs($this->getParameters());
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
     * @return ParameterInterface[]
     */
    private function getParameters(): array
    {
        if ($this->parameters === null) {
            $method = new ReflectionMethod($this->resolve[0], $this->resolve[1]);
            $this->parameters = $this->fieldsBuilder->getParameters($method);
        }
        return $this->parameters;
    }

    /**
     * @param object $source
     * @param array<string, mixed> $args
     * @param mixed $context
     * @param ResolveInfo $resolveInfo
     * @return object
     */
    public function resolve($source, array $args, $context, ResolveInfo $resolveInfo)
    {
        $parameters = $this->getParameters();

        $toPassArgs = [];
        foreach ($parameters as $parameter) {
            try {
                $toPassArgs[] = $parameter->resolve($source, $args, $context, $resolveInfo);
            } catch (MissingArgumentException $e) {
                if (is_string($this->resolve[0])) {
                    $factoryName = $this->resolve[0];
                } else {
                    $factoryName = get_class($this->resolve[0]);
                }
                throw MissingArgumentException::wrapWithFactoryContext($e, $this->name, $factoryName, $this->resolve[1]);
            }
        }

        $resolve = $this->resolve;

        return $resolve(...$toPassArgs);
    }
}
