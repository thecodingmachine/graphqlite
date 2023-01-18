<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Types;

use GraphQL\Error\ClientAware;
use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\AST\InputObjectTypeExtensionNode;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use ReflectionException;
use ReflectionMethod;
use TheCodingMachine\GraphQLite\Exceptions\GraphQLAggregateException;
use TheCodingMachine\GraphQLite\FieldsBuilder;
use TheCodingMachine\GraphQLite\InputTypeGenerator;
use TheCodingMachine\GraphQLite\InputTypeUtils;
use TheCodingMachine\GraphQLite\Parameters\MissingArgumentException;
use TheCodingMachine\GraphQLite\Parameters\ParameterInterface;

use function assert;
use function count;
use function is_array;
use function is_callable;

/**
 * A GraphQL input object that can be resolved using a factory
 *
 * @phpstan-import-type FieldConfig from InputObjectType
 */
class ResolvableMutableInputObjectType extends MutableInputObjectType implements ResolvableMutableInputInterface
{
    /** @var callable&array{object|string, string} */
    private $resolve;
    /** @var ParameterInterface[]|null */
    private array|null $parameters = null;
    /**
     * The list of decorator callables to be applied.
     *
     * @var array<int, callable&array<int, object|string>>
     */
    private array $decorators = [];
    /**
     * The list of decorator parameters to be applied.
     * The key matches the key of $this->decorators
     *
     * @var array<int, ParameterInterface[]>
     */
    private array $decoratorsParameters = [];

    /** @param array{name?: string|null,description?: string|null,parseValue?: callable(array<string, mixed>): mixed,astNode?: InputObjectTypeDefinitionNode|null,extensionASTNodes?: array<int, InputObjectTypeExtensionNode>|null} $additionalConfig */
    public function __construct(string $name, private FieldsBuilder $fieldsBuilder, object|string $factory, string $methodName, string|null $comment, private bool $canBeInstantiatedWithoutParameters, array $additionalConfig = [])
    {
        $resolve = [$factory, $methodName];
        assert(is_callable($resolve));
        $this->resolve       = $resolve;

        /**
         * @return iterable<FieldConfig>
         *
         * @throws ReflectionException
         */
        $fields = function (): array {
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
     *
     * @throws ReflectionException
     */
    private function getParameters(): array
    {
        if ($this->parameters === null) {
            $method           = new ReflectionMethod($this->resolve[0], $this->resolve[1]);
            $this->parameters = $this->fieldsBuilder->getParameters($method);
        }

        return $this->parameters;
    }

    /** @return ParameterInterface[] */
    private function getParametersForDecorator(int $key): array
    {
        if (! isset($this->decoratorsParameters[$key])) {
            $method                           = new ReflectionMethod($this->decorators[$key][0], $this->decorators[$key][1]);
            $this->decoratorsParameters[$key] = $this->fieldsBuilder->getParametersForDecorator($method);
        }

        return $this->decoratorsParameters[$key];
    }

    /** @param array<string, mixed> $args */
    public function resolve(object|null $source, array $args, mixed $context, ResolveInfo $resolveInfo): object
    {
        $parameters = $this->getParameters();

        $toPassArgs = [];

        $exceptions = [];
        foreach ($parameters as $parameter) {
            try {
                $toPassArgs[] = $parameter->resolve($source, $args, $context, $resolveInfo);
            } catch (MissingArgumentException $e) {
                throw MissingArgumentException::wrapWithFactoryContext($e, $this->name, $this->resolve);
            } catch (ClientAware $e) {
                $exceptions[] = $e;
            }
        }
        GraphQLAggregateException::throwExceptions($exceptions);

        $resolve = $this->resolve;

        $object = $resolve(...$toPassArgs);

        foreach ($this->decorators as $key => $decorator) {
            $decoratorParameters = $this->getParametersForDecorator($key);

            $toPassArgs = [$object];
            foreach ($decoratorParameters as $parameter) {
                try {
                    $toPassArgs[] = $parameter->resolve($source, $args, $context, $resolveInfo);
                } catch (MissingArgumentException $e) {
                    throw MissingArgumentException::wrapWithDecoratorContext($e, $this->name, $decorator);
                } catch (ClientAware $e) {
                    $exceptions[] = $e;
                }
            }
            GraphQLAggregateException::throwExceptions($exceptions);

            $object = $decorator(...$toPassArgs);
        }

        return $object;
    }

    /** @param callable&array<int, object|string> $decorator */
    public function decorate(callable $decorator): void
    {
        $this->decorators[] = $decorator;

        $key = count($this->decorators) - 1;

        $this->addFields(function () use ($key) {
            return InputTypeUtils::getInputTypeArgs($this->getParametersForDecorator($key));
        });

        if (! $this->canBeInstantiatedWithoutParameters || ! is_array($decorator)) {
            return;
        }

        $decoratorReflectionMethod = new ReflectionMethod($decorator[0], $decorator[1]);
        if (InputTypeGenerator::canBeInstantiatedWithoutParameter($decoratorReflectionMethod, true)) {
            return;
        }

        $this->canBeInstantiatedWithoutParameters = false;
    }

    public function isInstantiableWithoutParameters(): bool
    {
        return $this->canBeInstantiatedWithoutParameters;
    }
}
