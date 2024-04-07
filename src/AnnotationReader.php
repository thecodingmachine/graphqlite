<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
use TheCodingMachine\GraphQLite\Annotations\AbstractRequest;
use TheCodingMachine\GraphQLite\Annotations\Decorate;
use TheCodingMachine\GraphQLite\Annotations\EnumType;
use TheCodingMachine\GraphQLite\Annotations\Exceptions\ClassNotFoundException;
use TheCodingMachine\GraphQLite\Annotations\Exceptions\InvalidParameterException;
use TheCodingMachine\GraphQLite\Annotations\ExtendType;
use TheCodingMachine\GraphQLite\Annotations\Factory;
use TheCodingMachine\GraphQLite\Annotations\Input;
use TheCodingMachine\GraphQLite\Annotations\MiddlewareAnnotationInterface;
use TheCodingMachine\GraphQLite\Annotations\MiddlewareAnnotations;
use TheCodingMachine\GraphQLite\Annotations\ParameterAnnotationInterface;
use TheCodingMachine\GraphQLite\Annotations\ParameterAnnotations;
use TheCodingMachine\GraphQLite\Annotations\SourceFieldInterface;
use TheCodingMachine\GraphQLite\Annotations\Type;
use TheCodingMachine\GraphQLite\Annotations\TypeInterface;

use function array_diff_key;
use function array_filter;
use function array_key_exists;
use function array_map;
use function array_merge;
use function assert;
use function count;
use function get_class;
use function is_a;
use function reset;

class AnnotationReader
{
    /** @var array<string, (object|null)> */
    private array $methodAnnotationCache = [];

    /** @var array<string, array<object>> */
    private array $methodAnnotationsCache = [];

    /** @var array<string, array<object>> */
    private array $propertyAnnotationsCache = [];

    public function __construct()
    {
    }

    /**
     * Returns a class annotation. Does not look in the parent class.
     *
     * @param ReflectionClass<object> $refClass
     * @param class-string<T> $annotationClass
     *
     * @return T|null
     *
     * @throws ClassNotFoundException
     *
     * @template T of object
     */
    private function getClassAnnotation(ReflectionClass $refClass, string $annotationClass): object|null
    {
        $attribute = $refClass->getAttributes($annotationClass)[0] ?? null;
        if ($attribute) {
            $instance = $attribute->newInstance();
            assert($instance instanceof $annotationClass);
            return $instance;
        }

        return null;
    }

    /**
     * Returns a method annotation and handles correctly errors.
     *
     * @param class-string<object> $annotationClass
     */
    private function getMethodAnnotation(ReflectionMethod $refMethod, string $annotationClass): object|null
    {
        $cacheKey = $refMethod->getDeclaringClass()->getName() . '::' . $refMethod->getName() . '_' . $annotationClass;
        if (array_key_exists($cacheKey, $this->methodAnnotationCache)) {
            return $this->methodAnnotationCache[$cacheKey];
        }

        $attribute = $refMethod->getAttributes($annotationClass)[0] ?? null;
        if ($attribute) {
            return $this->methodAnnotationCache[$cacheKey] = $attribute->newInstance();
        }

        return $this->methodAnnotationCache[$cacheKey] = null;
    }

    /**
     * Returns the class annotations. Finds in the parents too.
     *
     * @param ReflectionClass<T> $refClass
     * @param class-string<A> $annotationClass
     *
     * @return A[]
     *
     * @template T of object
     * @template A of object
     */
    public function getClassAnnotations(ReflectionClass $refClass, string $annotationClass, bool $inherited = true): array
    {
        $toAddAnnotations = [];
        do {
            /** @var A[] $attributes */
            $attributes = array_map(
                static function ($attribute) {
                    return $attribute->newInstance();
                },
                array_filter($refClass->getAttributes(), static function ($annotation) use ($annotationClass): bool {
                    return is_a($annotation->getName(), $annotationClass, true);
                }),
            );

            $toAddAnnotations[] = $attributes;
            $refClass = $refClass->getParentClass();
        } while ($inherited && $refClass);

        if (count($toAddAnnotations) > 0) {
            return array_merge(...$toAddAnnotations);
        }

        return [];
    }

    /**
     * @param ReflectionClass<T> $refClass
     *
     * @throws ClassNotFoundException
     *
     * @template T of object
     */
    public function getTypeAnnotation(ReflectionClass $refClass): TypeInterface|null
    {
        try {
            $type = $this->getClassAnnotation($refClass, Type::class);

            if ($type !== null && $type->isSelfType()) {
                $type->setClass($refClass->getName());
            }
        } catch (ClassNotFoundException $e) {
            throw ClassNotFoundException::wrapException($e, $refClass->getName());
        }

        return $type;
    }

    /**
     * @param ReflectionClass<T> $refClass
     *
     * @return array<int,Input>
     *
     * @throws ClassNotFoundException
     *
     * @template T of object
     */
    public function getInputAnnotations(ReflectionClass $refClass): array
    {
        try {
            /** @var array<int,Input> $inputs */
            $inputs = $this->getClassAnnotations($refClass, Input::class, false);
            foreach ($inputs as $input) {
                $input->setClass($refClass->getName());
            }
        } catch (ClassNotFoundException $e) {
            throw ClassNotFoundException::wrapException($e, $refClass->getName());
        }

        return $inputs;
    }

    /**
     * @param ReflectionClass<T> $refClass
     *
     * @throws ClassNotFoundException
     *
     * @template T of object
     */
    public function getExtendTypeAnnotation(ReflectionClass $refClass): ExtendType|null
    {
        try {
            $extendType = $this->getClassAnnotation($refClass, ExtendType::class);
        } catch (ClassNotFoundException $e) {
            throw ClassNotFoundException::wrapExceptionForExtendTag($e, $refClass->getName());
        }

        return $extendType;
    }

    public function getEnumTypeAnnotation(ReflectionClass $refClass): EnumType|null
    {
        return $this->getClassAnnotation($refClass, EnumType::class);
    }

    /** @param class-string<AbstractRequest> $annotationClass */
    public function getRequestAnnotation(ReflectionMethod $refMethod, string $annotationClass): AbstractRequest|null
    {
        $queryAnnotation = $this->getMethodAnnotation($refMethod, $annotationClass);
        assert($queryAnnotation instanceof AbstractRequest || $queryAnnotation === null);

        return $queryAnnotation;
    }

    /**
     * @param ReflectionClass<T> $refClass
     *
     * @return SourceFieldInterface[]
     *
     * @template T of object
     */
    public function getSourceFields(ReflectionClass $refClass): array
    {
        /** @var SourceFieldInterface[] $sourceFields */
        $sourceFields = $this->getClassAnnotations($refClass, SourceFieldInterface::class);

        return $sourceFields;
    }

    public function getFactoryAnnotation(ReflectionMethod $refMethod): Factory|null
    {
        $factoryAnnotation = $this->getMethodAnnotation($refMethod, Factory::class);
        assert($factoryAnnotation instanceof Factory || $factoryAnnotation === null);

        return $factoryAnnotation;
    }

    public function getDecorateAnnotation(ReflectionMethod $refMethod): Decorate|null
    {
        $decorateAnnotation = $this->getMethodAnnotation($refMethod, Decorate::class);
        assert($decorateAnnotation instanceof Decorate || $decorateAnnotation === null);

        return $decorateAnnotation;
    }

    /**
     * @param ReflectionParameter[] $refParameters
     *
     * @return array<string, ParameterAnnotations>
     *
     * @throws InvalidParameterException
     */
    public function getParameterAnnotationsPerParameter(array $refParameters): array
    {
        if (empty($refParameters)) {
            return [];
        }
        $firstParam = reset($refParameters);

        $method = $firstParam->getDeclaringFunction();
        assert($method instanceof ReflectionMethod);

        /** @var ParameterAnnotationInterface[] $parameterAnnotations */
        $parameterAnnotations = $this->getMethodAnnotations($method, ParameterAnnotationInterface::class);

        /** @var array<string, array<int,ParameterAnnotations>> $parameterAnnotationsPerParameter */
        $parameterAnnotationsPerParameter = [];
        foreach ($parameterAnnotations as $parameterAnnotation) {
            $parameterAnnotationsPerParameter[$parameterAnnotation->getTarget()][] = $parameterAnnotation;
        }

        // Let's check that the referenced parameters actually do exist:
        $parametersByKey = [];
        foreach ($refParameters as $refParameter) {
            $parametersByKey[$refParameter->getName()] = true;
        }
        $diff = array_diff_key($parameterAnnotationsPerParameter, $parametersByKey);
        if (count($diff) > 0) {
            foreach ($diff as $parameterName => $parameterAnnotations) {
                throw InvalidParameterException::parameterNotFound($parameterName, get_class($parameterAnnotations[0]), $method);
            }
        }

        foreach ($refParameters as $refParameter) {
            $attributes = $refParameter->getAttributes();
            $parameterAnnotationsPerParameter[$refParameter->getName()] = [...$parameterAnnotationsPerParameter[$refParameter->getName()] ??
                [],
                ...array_map(
                    static function ($attribute) {
                        return $attribute->newInstance();
                    },
                    array_filter($attributes, static function ($annotation): bool {
                        return is_a($annotation->getName(), ParameterAnnotationInterface::class, true);
                    }),
                ),
            ];
        }

        return array_map(
            static function (array $parameterAnnotations): ParameterAnnotations {
                /** @var ParameterAnnotationInterface[] $parameterAnnotations */
                return new ParameterAnnotations($parameterAnnotations);
            },
            $parameterAnnotationsPerParameter,
        );
    }

    public function getMiddlewareAnnotations(ReflectionMethod|ReflectionProperty $reflection): MiddlewareAnnotations
    {
        if ($reflection instanceof ReflectionMethod) {
            $middlewareAnnotations = $this->getMethodAnnotations($reflection, MiddlewareAnnotationInterface::class);
        } else {
            $middlewareAnnotations = $this->getPropertyAnnotations($reflection, MiddlewareAnnotationInterface::class);
        }

        return new MiddlewareAnnotations($middlewareAnnotations);
    }

    /**
     * Returns the method's annotations.
     *
     * @param class-string<T> $annotationClass
     *
     * @return array<int, T>
     *
     * @template T of object
     */
    public function getMethodAnnotations(ReflectionMethod $refMethod, string $annotationClass): array
    {
        $cacheKey = $refMethod->getDeclaringClass()->getName() . '::' . $refMethod->getName() . '_s_' . $annotationClass;
        if (isset($this->methodAnnotationsCache[$cacheKey])) {
            /** @var array<int, T> $annotations */
            $annotations = $this->methodAnnotationsCache[$cacheKey];

            return $annotations;
        }

        $attributes = $refMethod->getAttributes();
        /** @var array<int, T> $toAddAnnotations */
        $toAddAnnotations = [
            ...array_map(
                static function ($attribute) {
                    return $attribute->newInstance();
                },
                array_filter($attributes, static function ($annotation) use ($annotationClass): bool {
                    return is_a($annotation->getName(), $annotationClass, true);
                }),
            ),
        ];

        $this->methodAnnotationsCache[$cacheKey] = $toAddAnnotations;

        return $toAddAnnotations;
    }

    /**
     * Returns the property's annotations.
     *
     * @param class-string<T> $annotationClass
     *
     * @return array<int, T>
     *
     * @template T of object
     */
    public function getPropertyAnnotations(ReflectionProperty $refProperty, string $annotationClass): array
    {
        $cacheKey = $refProperty->getDeclaringClass()->getName() . '::' . $refProperty->getName() . '_s_' . $annotationClass;
        if (isset($this->propertyAnnotationsCache[$cacheKey])) {
            /** @var array<int, T> $annotations */
            $annotations = $this->propertyAnnotationsCache[$cacheKey];

            return $annotations;
        }

        $attributes = $refProperty->getAttributes();
        /** @var array<int, T> $toAddAnnotations */
        $toAddAnnotations = [
            ...array_map(
                static function ($attribute) {
                    return $attribute->newInstance();
                },
                array_filter($attributes, static function ($annotation) use ($annotationClass): bool {
                    return is_a($annotation->getName(), $annotationClass, true);
                }),
            ),
        ];

        $this->propertyAnnotationsCache[$cacheKey] = $toAddAnnotations;

        return $toAddAnnotations;
    }
}
