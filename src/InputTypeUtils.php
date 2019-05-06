<?php


namespace TheCodingMachine\GraphQLite;

use function array_filter;
use function array_map;
use GraphQL\Type\Definition\InputType;
use phpDocumentor\Reflection\Fqsen;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Object_;
use phpDocumentor\Reflection\Types\Self_;
use ReflectionClass;
use ReflectionMethod;
use TheCodingMachine\GraphQLite\Parameters\InputTypeParameter;
use TheCodingMachine\GraphQLite\Parameters\ParameterInterface;

class InputTypeUtils
{
    /**
     * @var AnnotationReader
     */
    private $annotationReader;
    /**
     * @var NamingStrategyInterface
     */
    private $namingStrategy;

    public function __construct(AnnotationReader $annotationReader,
                                NamingStrategyInterface $namingStrategy)
    {
        $this->annotationReader = $annotationReader;
        $this->namingStrategy = $namingStrategy;
    }

    /**
     * Returns an array with 2 elements: [ $inputName, $className ]
     *
     * @param ReflectionMethod $method
     * @return string[]
     */
    public function getInputTypeNameAndClassName(ReflectionMethod $method): array
    {
        $fqsen = ltrim((string) $this->validateReturnType($method), '\\');
        $factory = $this->annotationReader->getFactoryAnnotation($method);
        if ($factory === null) {
            throw new \RuntimeException($method->getDeclaringClass()->getName().'::'.$method->getName().' has no @Factory annotation.');
        }
        return [$this->namingStrategy->getInputTypeName($fqsen, $factory), $fqsen];
    }

    private function validateReturnType(ReflectionMethod $refMethod): Fqsen
    {
        $returnType = $refMethod->getReturnType();
        if ($returnType === null) {
            throw MissingTypeHintException::missingReturnType($refMethod);
        }

        if ($returnType->allowsNull()) {
            throw MissingTypeHintException::nullableReturnType($refMethod);
        }

        $type = (string) $returnType;

        $typeResolver = new \phpDocumentor\Reflection\TypeResolver();

        $phpdocType = $typeResolver->resolve($type);
        $phpdocType = $this->resolveSelf($phpdocType, $refMethod->getDeclaringClass());
        if (!$phpdocType instanceof Object_) {
            throw MissingTypeHintException::invalidReturnType($refMethod);
        }

        return $phpdocType->getFqsen();
    }

    /**
     * Resolves "self" types into the class type.
     *
     * @param Type $type
     * @return Type
     */
    private function resolveSelf(Type $type, ReflectionClass $reflectionClass): Type
    {
        if ($type instanceof Self_) {
            return new Object_(new Fqsen('\\'.$reflectionClass->getName()));
        }
        return $type;
    }


    /**
     * Maps an array of ParameterInterface to an array of field descriptors as accepted by Webonyx.
     *
     * @param ParameterInterface[] $args
     * @return array<string, array<string, mixed|InputType>>
     */
    public static function getInputTypeArgs(array $args): array
    {
        $inputTypeArgs = array_filter($args, static function(ParameterInterface $parameter) { return $parameter instanceof InputTypeParameter; });
        return array_map(static function(InputTypeParameter $parameter) {
            $desc = [
                'type' => $parameter->getType()
            ];
            if ($parameter->hasDefaultValue()) {
                $desc['defaultValue'] = $parameter->getDefaultValue();
            }
            return $desc;
        }, $inputTypeArgs);
    }
}
