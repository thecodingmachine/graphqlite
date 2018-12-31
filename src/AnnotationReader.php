<?php


namespace TheCodingMachine\GraphQL\Controllers;


use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\Reader;
use function in_array;
use ReflectionClass;
use ReflectionMethod;
use function strpos;
use function substr;
use TheCodingMachine\GraphQL\Controllers\Annotations\AbstractRequest;
use TheCodingMachine\GraphQL\Controllers\Annotations\Exceptions\ClassNotFoundException;
use TheCodingMachine\GraphQL\Controllers\Annotations\Factory;
use TheCodingMachine\GraphQL\Controllers\Annotations\Logged;
use TheCodingMachine\GraphQL\Controllers\Annotations\Right;
use TheCodingMachine\GraphQL\Controllers\Annotations\SourceField;
use TheCodingMachine\GraphQL\Controllers\Annotations\Type;

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
     * @var bool
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
        // TODO: customize the way errors are handled here!
        try {
            /** @var Type|null $typeField */
            $typeField = $this->getClassAnnotation($refClass, Type::class);
        } catch (ClassNotFoundException $e) {
            throw ClassNotFoundException::wrapException($e, $refClass->getName());
        }
        return $typeField;
    }

    public function getRequestAnnotation(ReflectionMethod $refMethod, string $annotationName): ?AbstractRequest
    {
        /** @var AbstractRequest|null $queryAnnotation */
        $queryAnnotation = $this->reader->getMethodAnnotation($refMethod, $annotationName);
        return $queryAnnotation;
    }

    public function getLoggedAnnotation(ReflectionMethod $refMethod): ?Logged
    {
        /** @var Logged|null $loggedAnnotation */
        $loggedAnnotation = $this->reader->getMethodAnnotation($refMethod, Logged::class);
        return $loggedAnnotation;
    }

    public function getRightAnnotation(ReflectionMethod $refMethod): ?Right
    {
        /** @var Right|null $rightAnnotation */
        $rightAnnotation = $this->reader->getMethodAnnotation($refMethod, Right::class);
        return $rightAnnotation;
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
        $factoryAnnotation = $this->reader->getMethodAnnotation($refMethod, Factory::class);
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
            try {
                $type = $this->reader->getClassAnnotation($refClass, $annotationClass);
            } catch (AnnotationException $e) {
                if ($this->mode === self::STRICT_MODE) {
                    throw $e;
                } elseif ($this->mode === self::LAX_MODE) {
                    if ($this->isErrorImportant($annotationClass, $refClass->getDocComment(), $refClass->getName())) {
                        throw $e;
                    } else {
                        return null;
                    }
                }
            }
            if ($type !== null) {
                return $type;
            }
            $refClass = $refClass->getParentClass();
        } while ($refClass);
        return null;
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
