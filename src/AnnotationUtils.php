<?php


namespace TheCodingMachine\GraphQL\Controllers;


use Doctrine\Common\Annotations\Reader;
use ReflectionClass;

class AnnotationUtils
{
    /**
     * Returns a class annotation. Finds in the parents if not found in the main class.
     *
     * @return object|null
     */
    public static function getClassAnnotation(Reader $reader, ReflectionClass $refClass, string $annotationClass)
    {
        do {
            $type = $reader->getClassAnnotation($refClass, $annotationClass);
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
    public static function getClassAnnotations(Reader $reader, ReflectionClass $refClass): array
    {
        $annotations = [];
        do {
            $annotations = array_merge($reader->getClassAnnotations($refClass), $annotations);
            $refClass = $refClass->getParentClass();
        } while ($refClass);
        return $annotations;
    }
}