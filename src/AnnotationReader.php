<?php


namespace TheCodingMachine\GraphQLite;


use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\Reader;
use function in_array;
use ReflectionClass;
use ReflectionMethod;
use function strpos;
use function substr;
use TheCodingMachine\GraphQLite\Annotations\AbstractRequest;
use TheCodingMachine\GraphQLite\Annotations\Exceptions\ClassNotFoundException;
use TheCodingMachine\GraphQLite\Annotations\ExtendType;
use TheCodingMachine\GraphQLite\Annotations\Factory;
use TheCodingMachine\GraphQLite\Annotations\FailWith;
use TheCodingMachine\GraphQLite\Annotations\Logged;
use TheCodingMachine\GraphQLite\Annotations\Right;
use TheCodingMachine\GraphQLite\Annotations\SourceField;
use TheCodingMachine\GraphQLite\Annotations\Type;

class AnnotationReader
{
    /**
     * @var Reader
     */
    private $reader;

    // In this mode, no exceptions will be thrown for incorrect annotations (unless the name of the annotation we are looking for is part of the docblock)
    const LAX_MODE = 'LAX_MODE';
    // In this mode, exceptions will be thrown for any incorrect annotations.
    const STRICT_MODE = 'STRICT_MODE';

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
     * AnnotationReader constructor.
     * @param Reader $reader
     * @param string $mode One of self::LAX_MODE or self::STRICT_MODE
     * @param array $strictNamespaces
     */
    public function __construct(Reader $reader, string $mode = self::STRICT_MODE, array $strictNamespaces = [])
    {
        $this->reader = $reader;
        if (!in_array($mode, [self::LAX_MODE, self::STRICT_MODE], true)) {
            throw new \InvalidArgumentException('The mode passed must be one of AnnotationReader::LAX_MODE, AnnotationReader::STRICT_MODE');
        }
        $this->mode = $mode;
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

    public function getLoggedAnnotation(ReflectionMethod $refMethod): ?Logged
    {
        /** @var Logged|null $loggedAnnotation */
        $loggedAnnotation = $this->getMethodAnnotation($refMethod, Logged::class);
        return $loggedAnnotation;
    }

    public function getRightAnnotation(ReflectionMethod $refMethod): ?Right
    {
        /** @var Right|null $rightAnnotation */
        $rightAnnotation = $this->getMethodAnnotation($refMethod, Right::class);
        return $rightAnnotation;
    }

    public function getFailWithAnnotation(ReflectionMethod $refMethod): ?FailWith
    {
        /** @var FailWith|null $failWithAnnotation */
        $failWithAnnotation = $this->getMethodAnnotation($refMethod, FailWith::class);
        return $failWithAnnotation;
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

    /**
     * Returns a class annotation. Finds in the parents if not found in the main class.
     *
     * @return object|null
     */
    private function getClassAnnotation(ReflectionClass $refClass, string $annotationClass)
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
                        throw new \RuntimeException("Unexpected mode '$this->mode'."); // @codeCoverageIgnore
                }
            }
            if ($type !== null) {
                return $type;
            }
            $refClass = $refClass->getParentClass();
        } while ($refClass);
        return null;
    }
    
    private $methodAnnotationCache = [];

    /**
     * Returns a method annotation and handles correctly errors.
     *
     * @return object|null
     */
    private function getMethodAnnotation(ReflectionMethod $refMethod, string $annotationClass)
    {
        $cacheKey = $refMethod->getDeclaringClass()->getName().'::'.$refMethod->getName().'_'.$annotationClass;
        if (isset($this->methodAnnotationCache[$cacheKey])) {
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
                    throw new \RuntimeException("Unexpected mode '$this->mode'."); // @codeCoverageIgnore
            }
        }
    }

    /**
     * Returns true if the annotation class name is part of the docblock comment.
     *
     */
    private function isErrorImportant(string $annotationClass, string $docComment, string $className): bool
    {
        foreach ($this->strictNamespaces as $strictNamespace) {
            if (strpos($className, $strictNamespace) === 0) {
                return true;
            }
        }
        $shortAnnotationClass = substr($annotationClass, strrpos($annotationClass, '\\') + 1);
        return strpos($docComment, '@'.$shortAnnotationClass) !== false;
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
                $allAnnotations = $this->reader->getClassAnnotations($refClass);
                $toAddAnnotations[] = \array_filter($allAnnotations, function($annotation) use ($annotationClass): bool {
                    return $annotation instanceof $annotationClass;
                });
            } catch (AnnotationException $e) {
                if ($this->mode === self::STRICT_MODE) {
                    throw $e;
                } elseif ($this->mode === self::LAX_MODE) {
                    if ($this->isErrorImportant($annotationClass, $refClass->getDocComment(), $refClass->getName())) {
                        throw $e;
                    }
                }
            }
            $refClass = $refClass->getParentClass();
        } while ($refClass);

        if (!empty($toAddAnnotations)) {
            return array_merge(...$toAddAnnotations);
        } else {
            return [];
        }
    }
}
