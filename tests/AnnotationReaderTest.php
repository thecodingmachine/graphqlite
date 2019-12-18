<?php

namespace TheCodingMachine\GraphQLite;

use Doctrine\Common\Annotations\AnnotationException;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Doctrine\Common\Annotations\AnnotationReader as DoctrineAnnotationReader;
use ReflectionClass;
use ReflectionMethod;
use TheCodingMachine\GraphQLite\Annotations\Exceptions\ClassNotFoundException;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Type;
use TheCodingMachine\GraphQLite\Fixtures\Annotations\ClassWithInvalidClassAnnotation;
use TheCodingMachine\GraphQLite\Fixtures\Annotations\ClassWithInvalidExtendTypeAnnotation;
use TheCodingMachine\GraphQLite\Fixtures\Annotations\ClassWithInvalidTypeAnnotation;

class AnnotationReaderTest extends TestCase
{
    public function testBadConstructor(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new AnnotationReader(new DoctrineAnnotationReader(), 'foo');
    }

    public function testStrictMode(): void
    {
        $annotationReader = new AnnotationReader(new DoctrineAnnotationReader(), AnnotationReader::STRICT_MODE, []);

        $this->expectException(AnnotationException::class);
        $annotationReader->getTypeAnnotation(new ReflectionClass(ClassWithInvalidClassAnnotation::class));
    }

    public function testLaxModeWithBadAnnotation(): void
    {
        $annotationReader = new AnnotationReader(new DoctrineAnnotationReader(), AnnotationReader::LAX_MODE, []);

        $type = $annotationReader->getTypeAnnotation(new ReflectionClass(ClassWithInvalidClassAnnotation::class));
        $this->assertNull($type);
    }

    public function testLaxModeWithSmellyAnnotation(): void
    {
        $annotationReader = new AnnotationReader(new DoctrineAnnotationReader(), AnnotationReader::LAX_MODE, []);

        $this->expectException(AnnotationException::class);
        $annotationReader->getTypeAnnotation(new ReflectionClass(ClassWithInvalidTypeAnnotation::class));
    }

    public function testLaxModeWithBadAnnotationAndStrictNamespace(): void
    {
        $annotationReader = new AnnotationReader(new DoctrineAnnotationReader(), AnnotationReader::LAX_MODE, ['TheCodingMachine\\GraphQLite\\Fixtures']);

        $this->expectException(AnnotationException::class);
        $annotationReader->getTypeAnnotation(new ReflectionClass(ClassWithInvalidClassAnnotation::class));
    }

    public function testGetAnnotationsStrictMode(): void
    {
        $annotationReader = new AnnotationReader(new DoctrineAnnotationReader(), AnnotationReader::STRICT_MODE, []);

        $this->expectException(AnnotationException::class);
        $annotationReader->getClassAnnotations(new ReflectionClass(ClassWithInvalidClassAnnotation::class), Type::class);
    }

    public function testGetAnnotationsLaxModeWithBadAnnotation(): void
    {
        $annotationReader = new AnnotationReader(new DoctrineAnnotationReader(), AnnotationReader::LAX_MODE, []);

        $types = $annotationReader->getClassAnnotations(new ReflectionClass(ClassWithInvalidClassAnnotation::class), Type::class);
        $this->assertSame([], $types);
    }

    public function testGetAnnotationsLaxModeWithSmellyAnnotation(): void
    {
        $annotationReader = new AnnotationReader(new DoctrineAnnotationReader(), AnnotationReader::LAX_MODE, []);

        $this->expectException(AnnotationException::class);
        $annotationReader->getClassAnnotations(new ReflectionClass(ClassWithInvalidTypeAnnotation::class), Type::class);
    }

    public function testGetAnnotationsLaxModeWithBadAnnotationAndStrictNamespace(): void
    {
        $annotationReader = new AnnotationReader(new DoctrineAnnotationReader(), AnnotationReader::LAX_MODE, ['TheCodingMachine\\GraphQLite\\Fixtures']);

        $this->expectException(AnnotationException::class);
        $annotationReader->getClassAnnotations(new ReflectionClass(ClassWithInvalidClassAnnotation::class), Type::class);
    }

    public function testMethodStrictMode(): void
    {
        $annotationReader = new AnnotationReader(new DoctrineAnnotationReader(), AnnotationReader::STRICT_MODE, []);

        $this->expectException(AnnotationException::class);
        $annotationReader->getRequestAnnotation(new ReflectionMethod(ClassWithInvalidClassAnnotation::class, 'testMethod'), Field::class);
    }

    public function testMethodLaxModeWithBadAnnotation(): void
    {
        $annotationReader = new AnnotationReader(new DoctrineAnnotationReader(), AnnotationReader::LAX_MODE, []);

        $type = $annotationReader->getRequestAnnotation(new ReflectionMethod(ClassWithInvalidClassAnnotation::class, 'testMethod'), Field::class);
        $this->assertNull($type);
    }

    public function testMethodLaxModeWithSmellyAnnotation(): void
    {
        $annotationReader = new AnnotationReader(new DoctrineAnnotationReader(), AnnotationReader::LAX_MODE, []);

        $this->expectException(AnnotationException::class);
        $annotationReader->getRequestAnnotation(new ReflectionMethod(ClassWithInvalidTypeAnnotation::class, 'testMethod'), Field::class);
    }

    public function testExtendAnnotationException(): void
    {
        $annotationReader = new AnnotationReader(new DoctrineAnnotationReader(), AnnotationReader::STRICT_MODE, []);

        $this->expectException(ClassNotFoundException::class);
        $this->expectExceptionMessage("Could not autoload class 'foo' defined in @ExtendType annotation of class 'TheCodingMachine\GraphQLite\Fixtures\Annotations\ClassWithInvalidExtendTypeAnnotation'");
        $annotationReader->getExtendTypeAnnotation(new ReflectionClass(ClassWithInvalidExtendTypeAnnotation::class));
    }

    public function testMethodsStrictMode(): void
    {
        $annotationReader = new AnnotationReader(new DoctrineAnnotationReader(), AnnotationReader::STRICT_MODE, []);

        $this->expectException(AnnotationException::class);
        $annotationReader->getMethodAnnotations(new ReflectionMethod(ClassWithInvalidClassAnnotation::class, 'testMethod'), Field::class);
    }

    public function testMethodsLaxModeWithBadAnnotation(): void
    {
        $annotationReader = new AnnotationReader(new DoctrineAnnotationReader(), AnnotationReader::LAX_MODE, []);

        $type = $annotationReader->getMethodAnnotations(new ReflectionMethod(ClassWithInvalidClassAnnotation::class, 'testMethod'), Field::class);
        $this->assertSame([], $type);
    }

    public function testGetMethodsAnnotationsLaxModeWithBadAnnotationAndStrictNamespace(): void
    {
        $annotationReader = new AnnotationReader(new DoctrineAnnotationReader(), AnnotationReader::LAX_MODE, ['TheCodingMachine\\GraphQLite\\Fixtures']);

        $this->expectException(AnnotationException::class);
        $annotationReader->getMethodAnnotations(new ReflectionMethod(ClassWithInvalidClassAnnotation::class, 'testMethod'), Type::class);
    }

    public function testEmptyGetParameterAnnotations(): void
    {
        $annotationReader = new AnnotationReader(new DoctrineAnnotationReader());

        $this->assertEmpty($annotationReader->getParameterAnnotationsPerParameter([]));
    }
}
