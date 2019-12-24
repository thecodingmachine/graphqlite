<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\Reader;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use RuntimeException;
use TheCodingMachine\GraphQLite\Annotations\AbstractRequest;
use TheCodingMachine\GraphQLite\Annotations\Decorate;
use TheCodingMachine\GraphQLite\Annotations\Exceptions\ClassNotFoundException;
use TheCodingMachine\GraphQLite\Annotations\Exceptions\InvalidParameterException;
use TheCodingMachine\GraphQLite\Annotations\ExtendType;
use TheCodingMachine\GraphQLite\Annotations\Factory;
use TheCodingMachine\GraphQLite\Annotations\MiddlewareAnnotationInterface;
use TheCodingMachine\GraphQLite\Annotations\MiddlewareAnnotations;
use TheCodingMachine\GraphQLite\Annotations\ParameterAnnotationInterface;
use TheCodingMachine\GraphQLite\Annotations\ParameterAnnotations;
use TheCodingMachine\GraphQLite\Annotations\SourceFieldInterface;
use TheCodingMachine\GraphQLite\Annotations\Type;
use Webmozart\Assert\Assert;
use function array_diff_key;
use function array_filter;
use function array_key_exists;
use function array_map;
use function array_merge;
use function array_values;
use function assert;
use function get_class;
use function in_array;
use function reset;
use function strpos;
use function strrpos;
use function substr;

class AnnotationReader
{
    /** @var Reader */
    private $reader;

    // In this mode, no exceptions will be thrown for incorrect annotations (unless the name of the annotation we are looking for is part of the docblock)
    public const LAX_MODE = 'LAX_MODE';
    // In this mode, exceptions will be thrown for any incorrect annotations.
    public const STRICT_MODE = 'STRICT_MODE';

    /**
     * Classes in those namespaces MUST have valid annotations (otherwise, an error is thrown).
     *
     * @var string[]
     */
    private $strictNamespaces;

    /**
     * If true, no exceptions will be thrown for incorrect annotations in code coming from the "vendor/" directory.
     *
     * @var string
     */
    private $mode;

    /**
     * @param string   $mode             One of self::LAX_MODE or self::STRICT_MODE
     * @param string[] $strictNamespaces
     */
    public function __construct(Reader $reader, string $mode = self::STRICT_MODE, array $strictNamespaces = [])
    {
        $this->reader = $reader;
        if (! in_array($mode, [self::LAX_MODE, self::STRICT_MODE], true)) {
            throw new InvalidArgumentException('The mode passed must be one of AnnotationReader::LAX_MODE, AnnotationReader::STRICT_MODE');
        }
        $this->mode             = $mode;
        $this->strictNamespaces = $strictNamespaces;
    }

    /**
     * @param ReflectionClass<T> $refClass
     *
     * @template T of object
     */
    public function getTypeAnnotation(ReflectionClass $refClass): ?Type
    {
        try {
            $type = $this->getClassAnnotation($refClass, Type::class);
            assert($type instanceof Type || $type === null);
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
     * @template T of object
     */
    public function getExtendTypeAnnotation(ReflectionClass $refClass): ?ExtendType
    {
        try {
            $extendType = $this->getClassAnnotation($refClass, ExtendType::class);
            assert($extendType instanceof ExtendType || $extendType === null);
        } catch (ClassNotFoundException $e) {
            throw ClassNotFoundException::wrapExceptionForExtendTag($e, $refClass->getName());
        }

        return $extendType;
    }

    public function getRequestAnnotation(ReflectionMethod $refMethod, string $annotationName): ?AbstractRequest
    {
        $queryAnnotation = $this->getMethodAnnotation($refMethod, $annotationName);
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

    public function getFactoryAnnotation(ReflectionMethod $refMethod): ?Factory
    {
        $factoryAnnotation = $this->getMethodAnnotation($refMethod, Factory::class);
        assert($factoryAnnotation instanceof Factory || $factoryAnnotation === null);

        return $factoryAnnotation;
    }

    public function getDecorateAnnotation(ReflectionMethod $refMethod): ?Decorate
    {
        $decorateAnnotation = $this->getMethodAnnotation($refMethod, Decorate::class);
        assert($decorateAnnotation instanceof Decorate || $decorateAnnotation === null);

        return $decorateAnnotation;
    }

    /**
     * Only used in unit tests/
     *
     * @deprecated Use getParameterAnnotationsPerParameter instead
     *
     * @throws AnnotationException
     */
    public function getParameterAnnotations(ReflectionParameter $refParameter): ParameterAnnotations
    {
        $method = $refParameter->getDeclaringFunction();
        Assert::isInstanceOf($method, ReflectionMethod::class);
        /** @var ParameterAnnotationInterface[] $parameterAnnotations */
        $parameterAnnotations = $this->getMethodAnnotations($method, ParameterAnnotationInterface::class);
        $name = $refParameter->getName();

        $filteredAnnotations = array_values(array_filter($parameterAnnotations, static function (ParameterAnnotationInterface $parameterAnnotation) use ($name) {
            return $parameterAnnotation->getTarget() === $name;
        }));

        return new ParameterAnnotations($filteredAnnotations);
    }

    /**
     * @param ReflectionParameter[] $refParameters
     *
     * @return array<string, ParameterAnnotations>
     *
     * @throws AnnotationException
     */
    public function getParameterAnnotationsPerParameter(array $refParameters): array
    {
        if (empty($refParameters)) {
            return [];
        }
        $firstParam = reset($refParameters);

        $method = $firstParam->getDeclaringFunction();
        Assert::isInstanceOf($method, ReflectionMethod::class);

        /** @var ParameterAnnotationInterface[] $parameterAnnotations */
        $parameterAnnotations = $this->getMethodAnnotations($method, ParameterAnnotationInterface::class);

        /**
         * @var array<string, array<int, ParameterAnnotations>>
         */
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
        if (! empty($diff)) {
            foreach ($diff as $parameterName => $parameterAnnotations) {
                throw InvalidParameterException::parameterNotFound($parameterName, get_class($parameterAnnotations[0]), $method);
            }
        }

        return array_map(static function (array $parameterAnnotations) {
            return new ParameterAnnotations($parameterAnnotations);
        }, $parameterAnnotationsPerParameter);
    }

    public function getMiddlewareAnnotations(ReflectionMethod $refMethod): MiddlewareAnnotations
    {
        /** @var MiddlewareAnnotationInterface[] $middlewareAnnotations */
        $middlewareAnnotations = $this->getMethodAnnotations($refMethod, MiddlewareAnnotationInterface::class);

        return new MiddlewareAnnotations($middlewareAnnotations);
    }

    /**
     * Returns a class annotation. Does not look in the parent class.
     *
     * @param ReflectionClass<T> $refClass
     *
     * @template T of object
     */
    private function getClassAnnotation(ReflectionClass $refClass, string $annotationClass): ?object
    {
        $type = null;
        try {
            $type = $this->reader->getClassAnnotation($refClass, $annotationClass);
        } catch (AnnotationException $e) {
            switch ($this->mode) {
                case self::STRICT_MODE:
                    throw $e;
                case self::LAX_MODE:
                    if ($this->isErrorImportant($annotationClass, $refClass->getDocComment() ?: '', $refClass->getName())) {
                        throw $e;
                    } else {
                        return null;
                    }
                default:
                    throw new RuntimeException("Unexpected mode '" . $this->mode . "'."); // @codeCoverageIgnore
            }
        }

        return $type;
    }

    /** @var array<string, (object|null)> */
    private $methodAnnotationCache = [];

    /**
     * Returns a method annotation and handles correctly errors.
     */
    private function getMethodAnnotation(ReflectionMethod $refMethod, string $annotationClass): ?object
    {
        $cacheKey = $refMethod->getDeclaringClass()->getName() . '::' . $refMethod->getName() . '_' . $annotationClass;
        if (array_key_exists($cacheKey, $this->methodAnnotationCache)) {
            return $this->methodAnnotationCache[$cacheKey];
        }

        try {
            return $this->methodAnnotationCache[$cacheKey] = $this->reader->getMethodAnnotation($refMethod, $annotationClass);
        } catch (AnnotationException $e) {
            switch ($this->mode) {
                case self::STRICT_MODE:
                    throw $e;
                case self::LAX_MODE:
                    if ($this->isErrorImportant($annotationClass, $refMethod->getDocComment() ?: '', $refMethod->getDeclaringClass()->getName())) {
                        throw $e;
                    } else {
                        return null;
                    }
                default:
                    throw new RuntimeException("Unexpected mode '" . $this->mode . "'."); // @codeCoverageIgnore
            }
        }
    }

    /**
     * Returns true if the annotation class name is part of the docblock comment.
     */
    private function isErrorImportant(string $annotationClass, string $docComment, string $className): bool
    {
        foreach ($this->strictNamespaces as $strictNamespace) {
            if (strpos($className, $strictNamespace) === 0) {
                return true;
            }
        }
        $shortAnnotationClass = substr($annotationClass, strrpos($annotationClass, '\\') + 1);

        return strpos($docComment, '@' . $shortAnnotationClass) !== false;
    }

    /**
     * Returns the class annotations. Finds in the parents too.
     *
     * @param ReflectionClass<T> $refClass
     * @param class-string<A> $annotationClass
     *
     * @return A[]
     *
     * @throws AnnotationException
     *
     * @template T of object
     * @template A of object
     */
    public function getClassAnnotations(ReflectionClass $refClass, string $annotationClass): array
    {
        $toAddAnnotations = [];
        do {
            try {
                $allAnnotations     = $this->reader->getClassAnnotations($refClass);
                $toAddAnnotations[] = array_filter($allAnnotations, static function ($annotation) use ($annotationClass): bool {
                    return $annotation instanceof $annotationClass;
                });
            } catch (AnnotationException $e) {
                if ($this->mode === self::STRICT_MODE) {
                    throw $e;
                }

                if ($this->mode === self::LAX_MODE) {
                    if ($this->isErrorImportant($annotationClass, $refClass->getDocComment() ?: '', $refClass->getName())) {
                        throw $e;
                    }
                }
            }
            $refClass = $refClass->getParentClass();
        } while ($refClass);

        if (! empty($toAddAnnotations)) {
            return array_merge(...$toAddAnnotations);
        }

        return [];
    }

    /** @var array<string, array<object>> */
    private $methodAnnotationsCache = [];

    /**
     * Returns the method's annotations.
     *
     * @param class-string<T> $annotationClass
     *
     * @return array<int, T>
     *
     * @throws AnnotationException
     *
     * @template T of object
     */
    public function getMethodAnnotations(ReflectionMethod $refMethod, string $annotationClass): array
    {
        $cacheKey = $refMethod->getDeclaringClass()->getName() . '::' . $refMethod->getName() . '_s_' . $annotationClass;
        if (isset($this->methodAnnotationsCache[$cacheKey])) {
            return $this->methodAnnotationsCache[$cacheKey];
        }

        $toAddAnnotations = [];
        try {
            $allAnnotations   = $this->reader->getMethodAnnotations($refMethod);
            $toAddAnnotations = array_filter($allAnnotations, static function ($annotation) use ($annotationClass): bool {
                return $annotation instanceof $annotationClass;
            });
        } catch (AnnotationException $e) {
            if ($this->mode === self::STRICT_MODE) {
                throw $e;
            }

            if ($this->mode === self::LAX_MODE) {
                if ($this->isErrorImportant($annotationClass, $refMethod->getDocComment() ?: '', $refMethod->getDeclaringClass()->getName())) {
                    throw $e;
                }
            }
        }

        $this->methodAnnotationsCache[$cacheKey] = $toAddAnnotations;

        return $toAddAnnotations;
    }
}
