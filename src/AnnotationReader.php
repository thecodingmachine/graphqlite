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
use TheCodingMachine\GraphQLite\Annotations\ExtendType;
use TheCodingMachine\GraphQLite\Annotations\Factory;
use TheCodingMachine\GraphQLite\Annotations\MiddlewareAnnotationInterface;
use TheCodingMachine\GraphQLite\Annotations\MiddlewareAnnotations;
use TheCodingMachine\GraphQLite\Annotations\Parameter;
use TheCodingMachine\GraphQLite\Annotations\SourceField;
use TheCodingMachine\GraphQLite\Annotations\Type;
use Webmozart\Assert\Assert;
use function array_filter;
use function array_key_exists;
use function array_merge;
use function in_array;
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

    public function getTypeAnnotation(ReflectionClass $refClass): ?Type
    {
        try {
            /** @var Type|null $type */
            $type = $this->getClassAnnotation($refClass, Type::class);
            if ($type !== null && $type->isSelfType()) {
                $type->setClass($refClass->getName());
            }
        } catch (ClassNotFoundException $e) {
            throw ClassNotFoundException::wrapException($e, $refClass->getName());
        }

        return $type;
    }

    public function getExtendTypeAnnotation(ReflectionClass $refClass): ?ExtendType
    {
        try {
            /** @var ExtendType|null $extendType */
            $extendType = $this->getClassAnnotation($refClass, ExtendType::class);
        } catch (ClassNotFoundException $e) {
            throw ClassNotFoundException::wrapExceptionForExtendTag($e, $refClass->getName());
        }

        return $extendType;
    }

    public function getRequestAnnotation(ReflectionMethod $refMethod, string $annotationName): ?AbstractRequest
    {
        /** @var AbstractRequest|null $queryAnnotation */
        $queryAnnotation = $this->getMethodAnnotation($refMethod, $annotationName);

        return $queryAnnotation;
    }

    /**
     * @return SourceField[]
     */
    public function getSourceFields(ReflectionClass $refClass): array
    {
        /** @var SourceField[] $sourceFields */
        $sourceFields = $this->getClassAnnotations($refClass, SourceField::class);

        return $sourceFields;
    }

    public function getFactoryAnnotation(ReflectionMethod $refMethod): ?Factory
    {
        /** @var Factory|null $factoryAnnotation */
        $factoryAnnotation = $this->getMethodAnnotation($refMethod, Factory::class);

        return $factoryAnnotation;
    }

    public function getDecorateAnnotation(ReflectionMethod $refMethod): ?Decorate
    {
        /** @var Decorate|null $decorateAnnotation */
        $decorateAnnotation = $this->getMethodAnnotation($refMethod, Decorate::class);

        return $decorateAnnotation;
    }

    /**
     * @return Parameter[]
     */
    private function getParameterAnnotations(ReflectionMethod $refMethod): array
    {
        /** @var Parameter[] $useInputTypes */
        $useInputTypes = $this->getMethodAnnotations($refMethod, Parameter::class);

        return $useInputTypes;
    }

    public function getParameterAnnotation(ReflectionParameter $refParameter): ?Parameter
    {
        $declaringFunction = $refParameter->getDeclaringFunction();
        Assert::isInstanceOf($declaringFunction, ReflectionMethod::class, 'Parameter passed must be part of a method. Functions are not supported.');
        $annotations = $this->getParameterAnnotations($declaringFunction);
        foreach ($annotations as $annotation) {
            if ($annotation->getFor() === $refParameter->getName()) {
                return $annotation;
            }
        }

        return null;
    }

    public function getMiddlewareAnnotations(ReflectionMethod $refMethod): MiddlewareAnnotations
    {
        /** @var MiddlewareAnnotationInterface[] $middlewareAnnotations */
        $middlewareAnnotations = $this->getMethodAnnotations($refMethod, MiddlewareAnnotationInterface::class);

        return new MiddlewareAnnotations($middlewareAnnotations);
    }

    /**
     * Returns a class annotation. Finds in the parents if not found in the main class.
     */
    private function getClassAnnotation(ReflectionClass $refClass, string $annotationClass): ?object
    {
        do {
            $type = null;
            try {
                $type = $this->reader->getClassAnnotation($refClass, $annotationClass);
            } catch (AnnotationException $e) {
                switch ($this->mode) {
                    case self::STRICT_MODE:
                        throw $e;
                    case self::LAX_MODE:
                        if ($this->isErrorImportant($annotationClass, $refClass->getDocComment(), $refClass->getName())) {
                            throw $e;
                        } else {
                            return null;
                        }
                    default:
                        throw new RuntimeException("Unexpected mode '" . $this->mode . "'."); // @codeCoverageIgnore
                }
            }
            if ($type !== null) {
                return $type;
            }
            $refClass = $refClass->getParentClass();
        } while ($refClass);

        return null;
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
                    if ($this->isErrorImportant($annotationClass, $refMethod->getDocComment(), $refMethod->getDeclaringClass()->getName())) {
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
     * @return object[]
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
                    if ($this->isErrorImportant($annotationClass, $refClass->getDocComment(), $refClass->getName())) {
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
     * @return array<int, object>
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
                if ($this->isErrorImportant($annotationClass, $refMethod->getDocComment(), $refMethod->getDeclaringClass()->getName())) {
                    throw $e;
                }
            }
        }

        $this->methodAnnotationsCache[$cacheKey] = $toAddAnnotations;

        return $toAddAnnotations;
    }
}
