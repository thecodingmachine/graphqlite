<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use GraphQL\Type\Definition\InputObjectType;
use Psr\Container\ContainerInterface;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use TheCodingMachine\GraphQLite\Types\InputType;
use TheCodingMachine\GraphQLite\Types\InputTypeValidatorInterface;
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
    private $factoryCache = [];
    /** @var array<string, InputType> */
    private $inputCache = [];
    /** @var InputTypeUtils */
    private $inputTypeUtils;
    /** @var FieldsBuilder */
    private $fieldsBuilder;
    /** @var InputTypeValidatorInterface|null */
    private $inputTypeValidator;

    public function __construct(
        InputTypeUtils $inputTypeUtils,
        FieldsBuilder $fieldsBuilder,
        ?InputTypeValidatorInterface $inputTypeValidator = null
    ) {
        $this->inputTypeUtils = $inputTypeUtils;
        $this->fieldsBuilder  = $fieldsBuilder;
        $this->inputTypeValidator = $inputTypeValidator;
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

        if (! isset($this->factoryCache[$inputName])) {
            // TODO: add comment argument.
            $this->factoryCache[$inputName] = new ResolvableMutableInputObjectType($inputName, $this->fieldsBuilder, $object, $methodName, null, $this->canBeInstantiatedWithoutParameter($method, false));
        }

        return $this->factoryCache[$inputName];
    }

    /**
     * @param class-string<object> $className
     */
    public function mapInput(string $className, string $inputName, ?string $description, bool $isUpdate): InputType
    {
        if (! isset($this->inputCache[$inputName])) {
            $this->inputCache[$inputName] = new InputType(
                $className,
                $inputName,
                $description,
                $isUpdate,
                $this->fieldsBuilder,
                $this->inputTypeValidator
            );
        }

        return $this->inputCache[$inputName];
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
