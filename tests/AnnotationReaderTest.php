<?php

namespace TheCodingMachine\GraphQLite;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationReader as DoctrineAnnotationReader;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use TheCodingMachine\GraphQLite\Annotations\Autowire;
use TheCodingMachine\GraphQLite\Annotations\Exceptions\ClassNotFoundException;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Security;
use TheCodingMachine\GraphQLite\Annotations\Type;
use TheCodingMachine\GraphQLite\Fixtures\Annotations\ClassWithInvalidClassAnnotation;
use TheCodingMachine\GraphQLite\Fixtures\Annotations\ClassWithInvalidExtendTypeAnnotation;
use TheCodingMachine\GraphQLite\Fixtures\Annotations\ClassWithInvalidTypeAnnotation;
use TheCodingMachine\GraphQLite\Fixtures\Attributes\TestType;

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

    /**
     * @requires PHP >= 8.0
     */
    public function testPhp8AttributeClassAnnotation(): void
    {
        $annotationReader = new AnnotationReader(new DoctrineAnnotationReader());

        $type = $annotationReader->getTypeAnnotation(new ReflectionClass(TestType::class));
        $this->assertSame(TestType::class, $type->getClass());

        // We get the same instance
        // $type2 = $annotationReader->getTypeAnnotation(new ReflectionClass(TestType::class));
        // $this->assertSame($type, $type2, 'Assert some cache is available');
    }

    /**
     * @requires PHP >= 8.0
     */
    public function testPhp8AttributeClassAnnotations(): void
    {
        $annotationReader = new AnnotationReader(new DoctrineAnnotationReader());

        $types = $annotationReader->getSourceFields(new ReflectionClass(TestType::class));

        $this->assertCount(3, $types);
    }

    /**
     * @requires PHP >= 8.0
     */
    public function testPhp8AttributeMethodAnnotation(): void
    {
        $annotationReader = new AnnotationReader(new DoctrineAnnotationReader());

        $type = $annotationReader->getRequestAnnotation(new ReflectionMethod(TestType::class, 'getField'), Field::class);
        $this->assertInstanceOf(Field::class, $type);
    }

    /**
     * @requires PHP >= 8.0
     */
    public function testPhp8AttributeMethodAnnotations(): void
    {
        $annotationReader = new AnnotationReader(new DoctrineAnnotationReader());

        $middlewareAnnotations = $annotationReader->getMiddlewareAnnotations(new ReflectionMethod(TestType::class, 'getField'));

        /** @var Security[] $securitys */
        $securitys = $middlewareAnnotations->getAnnotationsByType(Security::class);
        $this->assertCount(2, $securitys);
        $this->assertFalse($securitys[0]->isFailWithSet());
        $this->assertNull($securitys[1]->getFailWith());
        $this->assertTrue($securitys[1]->isFailWithSet());
    }

    /**
     * @requires PHP >= 8.0
     */
    public function testPhp8AttributeParameterAnnotations(): void
    {
        $annotationReader = new AnnotationReader(new DoctrineAnnotationReader());

        $parameterAnnotations = $annotationReader->getParameterAnnotationsPerParameter((new ReflectionMethod(__CLASS__, 'method1'))->getParameters());

        $this->assertInstanceOf(Autowire::class, $parameterAnnotations['dao']->getAnnotationByType(Autowire::class));
    }

    private function method1(
        #[Autowire('myService')]
        $dao,
    ): void {
    }
}
