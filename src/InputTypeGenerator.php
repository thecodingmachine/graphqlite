<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use GraphQL\Type\Definition\InputObjectType;
use Psr\Container\ContainerInterface;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use TheCodingMachine\GraphQLite\Types\ResolvableMutableInputInterface;
use TheCodingMachine\GraphQLite\Types\ResolvableMutableInputObjectType;
use Webmozart\Assert\Assert;
use function array_shift;

/**
 * This class is in charge of creating Webonyx InputTypes from Factory annotations.
 */
class InputTypeGenerator
{
    /** @var array<string, ResolvableMutableInputObjectType> */
    private $cache = [];
    /** @var InputTypeUtils */
    private $inputTypeUtils;
    /** @var FieldsBuilder */
    private $fieldsBuilder;

    public function __construct(
        InputTypeUtils $inputTypeUtils,
        FieldsBuilder $fieldsBuilder
    ) {
        $this->inputTypeUtils = $inputTypeUtils;
        $this->fieldsBuilder  = $fieldsBuilder;
    }

    public function mapFactoryMethod(string $factory, string $methodName, ContainerInterface $container): ResolvableMutableInputObjectType
    {
        $method = new ReflectionMethod($factory, $methodName);

        if ($method->isStatic()) {
            $object = $factory;
        } else {
            $object = $container->get($factory);
        }

        [$inputName, $className] = $this->inputTypeUtils->getInputTypeNameAndClassName($method);

        if (! isset($this->cache[$inputName])) {
            // TODO: add comment argument.
            $this->cache[$inputName] = new ResolvableMutableInputObjectType($inputName, $this->fieldsBuilder, $object, $methodName, null, $this->canBeInstantiatedWithoutParameter($method, false));
        }

        return $this->cache[$inputName];
    }

    public static function canBeInstantiatedWithoutParameter(ReflectionFunctionAbstract $refMethod, bool $skipFirstArgument): bool
    {
        $nbParams = $refMethod->getNumberOfRequiredParameters();
        if (($nbParams === 0 && $skipFirstArgument === false) || ($nbParams <= 1 && $skipFirstArgument === true)) {
            return true;
        }

        $parameters = $refMethod->getParameters();

        if ($skipFirstArgument) {
            array_shift($parameters);
        }

        // Let's scan all the parameters. They must either have a default value or be nullable.
        foreach ($parameters as $parameter) {
            if (! $parameter->isDefaultValueAvailable() && ! $parameter->allowsNull()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param ResolvableMutableInputInterface&InputObjectType $inputType
     */
    public function decorateInputType(string $className, string $methodName, ResolvableMutableInputInterface $inputType, ContainerInterface $container): void
    {
        $method = new ReflectionMethod($className, $methodName);

        if ($method->isStatic()) {
            $object = $className;
        } else {
            $object = $container->get($className);
        }

        $callable = [$object, $methodName];
        Assert::isCallable($callable);
        $inputType->decorate($callable);
    }
}
