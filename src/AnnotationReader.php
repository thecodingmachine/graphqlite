<?php


namespace TheCodingMachine\GraphQL\Controllers;


use Doctrine\Common\Annotations\Reader;
use ReflectionClass;
use ReflectionMethod;
use TheCodingMachine\GraphQL\Controllers\Annotations\AbstractRequest;
use TheCodingMachine\GraphQL\Controllers\Annotations\Exceptions\ClassNotFoundException;
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

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    public function getTypeAnnotation(ReflectionClass $refClass): ?Type
    {
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
        $sourceFields = $this->getClassAnnotations($refClass);
        $sourceFields = \array_filter($sourceFields, function($annotation): bool {
            return $annotation instanceof SourceField;
        });
        return $sourceFields;
    }

    /**
     * Returns a class annotation. Finds in the parents if not found in the main class.
     *
     * @return object|null
     */
    private function getClassAnnotation(ReflectionClass $refClass, string $annotationClass)
    {
        do {
            $type = $this->reader->getClassAnnotation($refClass, $annotationClass);
            if ($type !== null) {
                return $type;
            }
            $refClass = $refClass->getParentClass();
        } while ($refClass);
        return null;
    }

    /**
     * Returns the class annotations. Finds in the parents too.
     *
     * @return object[]
     */
    public function getClassAnnotations(ReflectionClass $refClass): array
    {
        $annotations = [];
        do {
            $annotations = array_merge($this->reader->getClassAnnotations($refClass), $annotations);
            $refClass = $refClass->getParentClass();
        } while ($refClass);
        return $annotations;
    }

}
