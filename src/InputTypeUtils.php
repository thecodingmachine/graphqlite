<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

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
use TheCodingMachine\GraphQLite\Parameters\InputTypeParameterInterface;
use TheCodingMachine\GraphQLite\Parameters\ParameterInterface;
use Webmozart\Assert\Assert;
use function array_filter;
use function array_map;
use function assert;
use function ltrim;

class InputTypeUtils
{
    /** @var AnnotationReader */
    private $annotationReader;
    /** @var NamingStrategyInterface */
    private $namingStrategy;

    public function __construct(
        AnnotationReader $annotationReader,
        NamingStrategyInterface $namingStrategy
    ) {
        $this->annotationReader = $annotationReader;
        $this->namingStrategy   = $namingStrategy;
    }

    /**
     * Returns an array with 2 elements: [ $inputName, $className ]
     *
     * @return string[]
     */
    public function getInputTypeNameAndClassName(ReflectionMethod $method): array
    {
        $fqsen   = ltrim((string) $this->validateReturnType($method), '\\');
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
        Assert::notNull($phpdocType);
        $phpdocType = $this->resolveSelf($phpdocType, $refMethod->getDeclaringClass());
        if (! $phpdocType instanceof Object_) {
            throw MissingTypeHintRuntimeException::invalidReturnType($refMethod);
        }

        $fqsen = $phpdocType->getFqsen();
        Assert::notNull($fqsen);

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
     * Maps an array of ParameterInterface to an array of field descriptors as accepted by Webonyx.
     *
     * @param ParameterInterface[] $args
     *
     * @return array<string, array<string, mixed|InputType>>
     */
    public static function getInputTypeArgs(array $args): array
    {
        $inputTypeArgs = array_filter($args, static function (ParameterInterface $parameter) {
            return $parameter instanceof InputTypeParameterInterface;
        });

        return array_map(static function (InputTypeParameterInterface $parameter) {
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
