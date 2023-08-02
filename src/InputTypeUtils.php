<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use GraphQL\Type\Definition\Argument;
use GraphQL\Type\Definition\InputObjectField;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\InputType;
use phpDocumentor\Reflection\Fqsen;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\Object_;
use phpDocumentor\Reflection\Types\Self_;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use RuntimeException;
use TheCodingMachine\GraphQLite\Parameters\ExpandsInputTypeParameters;
use TheCodingMachine\GraphQLite\Parameters\InputTypeParameterInterface;
use TheCodingMachine\GraphQLite\Parameters\ParameterInterface;

use function array_map;
use function assert;
use function ltrim;

/**
 * @phpstan-import-type FieldConfig from InputObjectType
 * @phpstan-import-type ArgumentConfig from Argument
 * @phpstan-import-type InputObjectFieldConfig from InputObjectField
 */
class InputTypeUtils
{
    public function __construct(
        private readonly AnnotationReader $annotationReader,
        private readonly NamingStrategyInterface $namingStrategy,
    )
    {
    }

    /**
     * Returns an array with 2 elements: [ $inputName, $className ]
     *
     * @return array{0: string, 1:class-string<object>}
     */
    public function getInputTypeNameAndClassName(ReflectionMethod $method): array
    {
        /** @var class-string<object> $fqsen */
        $fqsen = ltrim((string) $this->validateReturnType($method), '\\');
        $factory = $this->annotationReader->getFactoryAnnotation($method);
        if ($factory === null) {
            throw new RuntimeException($method->getDeclaringClass()->getName() . '::' . $method->getName() . ' has no @Factory annotation.');
        }

        return [$this->namingStrategy->getInputTypeName($fqsen, $factory), $fqsen];
    }

    private function validateReturnType(ReflectionMethod $refMethod): Fqsen
    {
        $returnType = $refMethod->getReturnType();
        if ($returnType === null) {
            throw MissingTypeHintRuntimeException::missingReturnType($refMethod);
        }
        assert($returnType instanceof ReflectionNamedType);

        if ($returnType->allowsNull()) {
            throw MissingTypeHintRuntimeException::nullableReturnType($refMethod);
        }

        $type = $returnType->getName();

        $typeResolver = new TypeResolver();

        $phpdocType = $typeResolver->resolve($type);
        assert($phpdocType !== null);
        $phpdocType = $this->resolveSelf($phpdocType, $refMethod->getDeclaringClass());
        if (! $phpdocType instanceof Object_) {
            throw MissingTypeHintRuntimeException::invalidReturnType($refMethod);
        }

        $fqsen = $phpdocType->getFqsen();
        assert($fqsen !== null);

        return $fqsen;
    }

    /**
     * Resolves "self" types into the class type.
     *
     * @param ReflectionClass<object> $reflectionClass
     */
    private function resolveSelf(Type $type, ReflectionClass $reflectionClass): Type
    {
        if ($type instanceof Self_) {
            return new Object_(new Fqsen('\\' . $reflectionClass->getName()));
        }

        return $type;
    }

    /**
     * @param array<string, ParameterInterface> $parameters
     *
     * @return array<string, InputTypeParameterInterface>
     */
    public static function toInputParameters(array $parameters): array
    {
        $result = [];

        foreach ($parameters as $name => $parameter) {
            if ($parameter instanceof InputTypeParameterInterface) {
                $result[$name] = $parameter;
            }

            if (! ($parameter instanceof ExpandsInputTypeParameters)) {
                continue;
            }

            $result = [
                ...$result,
                ...$parameter->toInputTypeParameters(),
            ];
        }

        return $result;
    }

    /**
     * Maps an array of ParameterInterface to an array of field descriptors as accepted by Webonyx.
     *
     * @param ParameterInterface[] $args
     *
     * @return array{defaultValue?:mixed,type:\GraphQL\Type\Definition\Type&InputType}[]
     */
    public static function getInputTypeArgs(array $args): array
    {
        $inputTypeArgs = self::toInputParameters($args);

        return array_map(static function (InputTypeParameterInterface $parameter): array {
            $desc = [
                'type' => $parameter->getType(),
            ];
            if ($parameter->hasDefaultValue()) {
                $desc['defaultValue'] = $parameter->getDefaultValue();
            }

            return $desc;
        }, $inputTypeArgs);
    }
}
