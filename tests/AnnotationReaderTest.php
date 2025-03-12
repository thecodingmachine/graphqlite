<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

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
    public function testBadAnnotation(): void
    {
        $annotationReader = new AnnotationReader();

        $type = $annotationReader->getTypeAnnotation(
            new ReflectionClass(ClassWithInvalidClassAnnotation::class),
        );

        $this->assertNull($type);
    }

    public function testSmellyAnnotation(): void
    {
        $annotationReader = new AnnotationReader();

        $this->assertNull($annotationReader->getTypeAnnotation(
            new ReflectionClass(ClassWithInvalidTypeAnnotation::class)),
        );
    }

    public function testGetAnnotationsWithBadAnnotation(): void
    {
        $annotationReader = new AnnotationReader();

        $types = $annotationReader->getClassAnnotations(
            new ReflectionClass(ClassWithInvalidClassAnnotation::class),
            Type::class,
        );

        $this->assertSame([], $types);
    }

    public function testMethodWithBadAnnotation(): void
    {
        $annotationReader = new AnnotationReader();

        $type = $annotationReader->getRequestAnnotation(
            new ReflectionMethod(ClassWithInvalidClassAnnotation::class, 'testMethod'),
            Field::class,
        );
        $this->assertNull($type);
    }

    public function testExtendAnnotationException(): void
    {
        $annotationReader = new AnnotationReader();

        $this->expectException(ClassNotFoundException::class);
        $this->expectExceptionMessage("Could not autoload class 'foo' defined in #[ExtendType] attribute of class 'TheCodingMachine\GraphQLite\Fixtures\Annotations\ClassWithInvalidExtendTypeAnnotation'");
        $annotationReader->getExtendTypeAnnotation(
            new ReflectionClass(ClassWithInvalidExtendTypeAnnotation::class),
        );
    }

    public function testMethodsWithBadAnnotation(): void
    {
        $annotationReader = new AnnotationReader();

        $type = $annotationReader->getMethodAnnotations(
            new ReflectionMethod(ClassWithInvalidClassAnnotation::class, 'testMethod'),
            Field::class,
        );

        $this->assertSame([], $type);
    }

    public function testEmptyGetParameterAnnotations(): void
    {
        $annotationReader = new AnnotationReader();

        $this->assertEmpty($annotationReader->getParameterAnnotationsPerParameter([]));
    }

    public function testPhp8AttributeClassAnnotation(): void
    {
        $annotationReader = new AnnotationReader();

        $type = $annotationReader->getTypeAnnotation(new ReflectionClass(TestType::class));
        $this->assertSame(TestType::class, $type->getClass());

        // We get the same instance
        //$type2 = $annotationReader->getTypeAnnotation(new ReflectionClass(TestType::class));
        //$this->assertSame($type, $type2, 'Assert some cache is available');
    }

    public function testPhp8AttributeClassAnnotations(): void
    {
        $annotationReader = new AnnotationReader();

        $types = $annotationReader->getSourceFields(new ReflectionClass(TestType::class));

        $this->assertCount(3, $types);
    }

    public function testPhp8AttributeMethodAnnotation(): void
    {
        $annotationReader = new AnnotationReader();

        $type = $annotationReader->getRequestAnnotation(
            new ReflectionMethod(TestType::class, 'getField'),
            Field::class,
        );

        $this->assertInstanceOf(Field::class, $type);
    }

    public function testPhp8AttributeMethodAnnotations(): void
    {
        $annotationReader = new AnnotationReader();

        $middlewareAnnotations = $annotationReader->getMiddlewareAnnotations(
            new ReflectionMethod(TestType::class, 'getField'),
        );

        /** @var Security[] $securitys */
        $securitys = $middlewareAnnotations->getAnnotationsByType(Security::class);
        $this->assertCount(2, $securitys);
        $this->assertFalse($securitys[0]->isFailWithSet());
        $this->assertNull($securitys[1]->getFailWith());
        $this->assertTrue($securitys[1]->isFailWithSet());
    }

    public function testPhp8AttributeParameterAnnotations(): void
    {
        $annotationReader = new AnnotationReader();

        $parameterAnnotations = $annotationReader->getParameterAnnotationsPerParameter(
            (new ReflectionMethod(self::class, 'method1'))->getParameters(),
        );

        $this->assertInstanceOf(
            Autowire::class,
            $parameterAnnotations['dao']->getAnnotationByType(Autowire::class),
        );
    }

    /** @noinspection PhpUnusedPrivateMethodInspection Used in {@see testPhp8AttributeParameterAnnotations} */
    private function method1(
        #[Autowire('myService')]
        $dao,
    ): void {
    }
}
