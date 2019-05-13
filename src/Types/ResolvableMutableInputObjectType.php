<?php


namespace TheCodingMachine\GraphQLite\Types;

use GraphQL\Type\Definition\ResolveInfo;
use ReflectionMethod;
use TheCodingMachine\GraphQLite\FieldsBuilder;
use TheCodingMachine\GraphQLite\InputTypeUtils;
use TheCodingMachine\GraphQLite\Parameters\MissingArgumentException;
use TheCodingMachine\GraphQLite\Parameters\ParameterInterface;

/**
 * A GraphQL input object that can be resolved using a factory
 */
class ResolvableMutableInputObjectType extends MutableInputObjectType implements ResolvableMutableInputInterface
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
     * The list of decorator callables to be applied.
     *
     * @var array<int, callable&array<int, object|string>>
     */
    private $decorators = [];
    /**
     * The list of decorator parameters to be applied.
     * The key matches the key of $this->decorators
     *
     * @var array<int, ParameterInterface[]>
     */
    private $decoratorsParameters = [];

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
     * @return ParameterInterface[]
     */
    private function getParametersForDecorator(int $key): array
    {
        if (!isset($this->decoratorsParameters[$key])) {
            $method = new ReflectionMethod($this->decorators[$key][0], $this->decorators[$key][1]);
            $this->decoratorsParameters[$key] = $this->fieldsBuilder->getParametersForDecorator($method);
        }
        return $this->decoratorsParameters[$key];
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
                throw MissingArgumentException::wrapWithFactoryContext($e, $this->name, $this->resolve);
            }
        }

        $resolve = $this->resolve;

        $object = $resolve(...$toPassArgs);

        foreach ($this->decorators as $key => $decorator) {
            $decoratorParameters = $this->getParametersForDecorator($key);

            $toPassArgs = [ $object ];
            foreach ($decoratorParameters as $parameter) {
                try {
                    $toPassArgs[] = $parameter->resolve($source, $args, $context, $resolveInfo);
                } catch (MissingArgumentException $e) {
                    throw MissingArgumentException::wrapWithDecoratorContext($e, $this->name, $decorator);
                }
            }

            $object = $decorator(...$toPassArgs);
        }

        return $object;
    }

    public function decorate(callable $decorator): void
    {
        $this->decorators[] = $decorator;

        $key = count($this->decorators)-1;

        $this->addFields(function() use ($key) {
            return InputTypeUtils::getInputTypeArgs($this->getParametersForDecorator($key));
        });
    }
}
