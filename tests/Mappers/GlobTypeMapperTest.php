<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Mappers;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use stdClass;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\NullAdapter;
use Symfony\Component\Cache\Psr16Cache;
use TheCodingMachine\GraphQLite\AbstractQueryProvider;
use TheCodingMachine\GraphQLite\AnnotationReader;
use TheCodingMachine\GraphQLite\Annotations\Exceptions\ClassNotFoundException;
use TheCodingMachine\GraphQLite\Containers\LazyContainer;
use TheCodingMachine\GraphQLite\FailedResolvingInputType;
use TheCodingMachine\GraphQLite\Fixtures\BadExtendType\BadExtendType;
use TheCodingMachine\GraphQLite\Fixtures\BadExtendType2\BadExtendType2;
use TheCodingMachine\GraphQLite\Fixtures\InheritedInputTypes\ChildTestFactory;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Types\FilterDecorator;
use TheCodingMachine\GraphQLite\Fixtures\Mocks\MockResolvableInputObjectType;
use TheCodingMachine\GraphQLite\Fixtures\NonInstantiableInput\AbstractFoo;
use TheCodingMachine\GraphQLite\Fixtures\TestInput;
use TheCodingMachine\GraphQLite\Fixtures\TestObject;
use TheCodingMachine\GraphQLite\Fixtures\TestType;
use TheCodingMachine\GraphQLite\Fixtures\Types\FooExtendType;
use TheCodingMachine\GraphQLite\Fixtures\Types\FooType;
use TheCodingMachine\GraphQLite\Fixtures\Types\TestFactory;
use TheCodingMachine\GraphQLite\GraphQLRuntimeException;
use TheCodingMachine\GraphQLite\NamingStrategy;
use TheCodingMachine\GraphQLite\Types\MutableObjectType;

class GlobTypeMapperTest extends AbstractQueryProvider
{
    public function testGlobTypeMapper(): void
    {
        $container = new LazyContainer([
            FooType::class => static function () {
                return new FooType();
            },
        ]);

        $typeGenerator = $this->getTypeGenerator();
        $inputTypeGenerator = $this->getInputTypeGenerator();

        $cache = new Psr16Cache(new ArrayAdapter());

        $mapper = new GlobTypeMapper($this->getClassFinder('TheCodingMachine\GraphQLite\Fixtures\Types'), $typeGenerator, $inputTypeGenerator, $this->getInputTypeUtils(), $container, new AnnotationReader(), new NamingStrategy(), $this->getTypeMapper(), $cache);

        $this->assertSame([TestObject::class], $mapper->getSupportedClasses());
        $this->assertTrue($mapper->canMapClassToType(TestObject::class));
        $this->assertInstanceOf(ObjectType::class, $mapper->mapClassToType(TestObject::class, null));
        $this->assertInstanceOf(ObjectType::class, $mapper->mapNameToType('Foo', $this->getTypeMapper()));
        $this->assertTrue($mapper->canMapNameToType('Foo'));
        $this->assertFalse($mapper->canMapNameToType('NotExists'));

        // Again to test cache
        $anotherMapperSameCache = new GlobTypeMapper($this->getClassFinder('TheCodingMachine\GraphQLite\Fixtures\Types'), $typeGenerator, $this->getInputTypeGenerator(), $this->getInputTypeUtils(), $container, new AnnotationReader(), new NamingStrategy(), $this->getTypeMapper(), $cache);
        $this->assertTrue($anotherMapperSameCache->canMapClassToType(TestObject::class));
        $this->assertTrue($anotherMapperSameCache->canMapNameToType('Foo'));

        $this->expectException(CannotMapTypeException::class);
        $mapper->mapClassToType(stdClass::class, null);
    }

    public function testGlobTypeMapperDuplicateTypesException(): void
    {
        $container = new LazyContainer([
            TestType::class => static function () {
                return new TestType();
            },
        ]);

        $typeGenerator = $this->getTypeGenerator();

        $mapper = new GlobTypeMapper($this->getClassFinder('TheCodingMachine\GraphQLite\Fixtures\DuplicateTypes'), $typeGenerator, $this->getInputTypeGenerator(), $this->getInputTypeUtils(), $container, new AnnotationReader(), new NamingStrategy(), $this->getTypeMapper(), new Psr16Cache(new NullAdapter()));

        $this->expectException(DuplicateMappingException::class);
        $mapper->canMapClassToType(TestType::class);
    }

    public function testGlobTypeMapperDuplicateInputsException(): void
    {
        $container = new LazyContainer([
            TestInput::class => static function () {
                return new TestInput();
            },
        ]);

        $typeGenerator = $this->getTypeGenerator();

        $mapper = new GlobTypeMapper($this->getClassFinder('TheCodingMachine\GraphQLite\Fixtures\DuplicateInputs'), $typeGenerator, $this->getInputTypeGenerator(), $this->getInputTypeUtils(), $container, new AnnotationReader(), new NamingStrategy(), $this->getTypeMapper(), new Psr16Cache(new NullAdapter()));

        $this->expectException(DuplicateMappingException::class);
        $mapper->canMapClassToInputType(TestInput::class);
    }

    public function testGlobTypeMapperDuplicateInputTypesException(): void
    {
        $container = new LazyContainer([
            /*TestType::class => function() {
                return new TestType();
            }*/
        ]);

        $typeGenerator = $this->getTypeGenerator();

        $mapper = new GlobTypeMapper($this->getClassFinder('TheCodingMachine\GraphQLite\Fixtures\DuplicateInputTypes'), $typeGenerator, $this->getInputTypeGenerator(), $this->getInputTypeUtils(), $container, new AnnotationReader(), new NamingStrategy(), $this->getTypeMapper(), new Psr16Cache(new NullAdapter()));

        $caught = false;
        try {
            $mapper->canMapClassToInputType(TestObject::class);
        } catch (DuplicateMappingException $e) {
            // Depending on the environment, one of the messages can be returned.
            $this->assertContains(
                $e->getMessage(),
                [
                    'The class \'TheCodingMachine\GraphQLite\Fixtures\TestObject\' should be mapped to only one GraphQL Input type. Two methods are pointing via the @Factory annotation to this class: \'TheCodingMachine\GraphQLite\Fixtures\DuplicateInputTypes\TestFactory::myFactory\' and \'TheCodingMachine\GraphQLite\Fixtures\DuplicateInputTypes\TestFactory2::myFactory\'',
                    'The class \'TheCodingMachine\GraphQLite\Fixtures\TestObject\' should be mapped to only one GraphQL Input type. Two methods are pointing via the @Factory annotation to this class: \'TheCodingMachine\GraphQLite\Fixtures\DuplicateInputTypes\TestFactory2::myFactory\' and \'TheCodingMachine\GraphQLite\Fixtures\DuplicateInputTypes\TestFactory::myFactory\'',
                ],
            );
            $caught = true;
        }
        $this->assertTrue($caught, 'DuplicateMappingException is thrown');
    }

    public function testGlobTypeMapperInheritedInputTypesException(): void
    {
        $container = new LazyContainer([
            ChildTestFactory::class => static function () {
                return new ChildTestFactory();
            },
        ]);

        $typeGenerator = $this->getTypeGenerator();

        $mapper = new GlobTypeMapper($this->getClassFinder('TheCodingMachine\GraphQLite\Fixtures\InheritedInputTypes'), $typeGenerator, $this->getInputTypeGenerator(), $this->getInputTypeUtils(), $container, new AnnotationReader(), new NamingStrategy(), $this->getTypeMapper(), new Psr16Cache(new NullAdapter()));

        //$this->expectException(DuplicateMappingException::class);
        //$this->expectExceptionMessage('The class \'TheCodingMachine\GraphQLite\Fixtures\TestObject\' should be mapped to only one GraphQL Input type. Two methods are pointing via the @Factory annotation to this class: \'TheCodingMachine\GraphQLite\Fixtures\DuplicateInputTypes\TestFactory::myFactory\' and \'TheCodingMachine\GraphQLite\Fixtures\DuplicateInputTypes\TestFactory2::myFactory\'');
        $this->assertTrue($mapper->canMapClassToInputType(TestObject::class));
        $mapper->mapClassToInputType(TestObject::class);
    }

    public function testGlobTypeMapperClassNotFoundException(): void
    {
        $container = new LazyContainer([
            TestType::class => static function () {
                return new TestType();
            },
        ]);

        $typeGenerator = $this->getTypeGenerator();

        $mapper = new GlobTypeMapper($this->getClassFinder('TheCodingMachine\GraphQLite\Fixtures\BadClassType'), $typeGenerator, $this->getInputTypeGenerator(), $this->getInputTypeUtils(), $container, new AnnotationReader(), new NamingStrategy(), $this->getTypeMapper(), new Psr16Cache(new NullAdapter()));

        $this->expectException(ClassNotFoundException::class);
        $this->expectExceptionMessage("Could not autoload class 'Foobar' defined in #[Type] attribute of class 'TheCodingMachine\\GraphQLite\\Fixtures\\BadClassType\\TestType'");
        $mapper->canMapClassToType(TestType::class);
    }

    public function testGlobTypeMapperNameNotFoundException(): void
    {
        $container = new LazyContainer([
            FooType::class => static function () {
                return new FooType();
            },
        ]);

        $typeGenerator = $this->getTypeGenerator();

        $mapper = new GlobTypeMapper($this->getClassFinder('TheCodingMachine\GraphQLite\Fixtures\Types'), $typeGenerator, $this->getInputTypeGenerator(), $this->getInputTypeUtils(), $container, new AnnotationReader(), new NamingStrategy(), $this->getTypeMapper(), new Psr16Cache(new NullAdapter()));

        $this->expectException(CannotMapTypeException::class);
        $mapper->mapNameToType('NotExists', $this->getTypeMapper());
    }

    public function testGlobTypeMapperInputType(): void
    {
        $container = new LazyContainer([
            FooType::class => static function () {
                return new FooType();
            },
            TestFactory::class => static function () {
                return new TestFactory();
            },
        ]);

        $typeGenerator = $this->getTypeGenerator();

        $cache = new Psr16Cache(new ArrayAdapter());

        $mapper = new GlobTypeMapper($this->getClassFinder('TheCodingMachine\GraphQLite\Fixtures\Types'), $typeGenerator, $this->getInputTypeGenerator(), $this->getInputTypeUtils(), $container, new AnnotationReader(), new NamingStrategy(), $this->getTypeMapper(), $cache);

        $this->assertTrue($mapper->canMapClassToInputType(TestObject::class));

        $inputType = $mapper->mapClassToInputType(TestObject::class, $this->getTypeMapper());

        $this->assertSame('TestObjectInput', $inputType->name);

        // Again to test cache
        $anotherMapperSameCache = new GlobTypeMapper($this->getClassFinder('TheCodingMachine\GraphQLite\Fixtures\Types'), $typeGenerator, $this->getInputTypeGenerator(), $this->getInputTypeUtils(), $container, new AnnotationReader(), new NamingStrategy(), $this->getTypeMapper(), $cache);

        $this->assertTrue($anotherMapperSameCache->canMapClassToInputType(TestObject::class));
        $this->assertSame('TestObjectInput', $anotherMapperSameCache->mapClassToInputType(TestObject::class, $this->getTypeMapper())->name);

        $this->expectException(CannotMapTypeException::class);
        $mapper->mapClassToInputType(TestType::class, $this->getTypeMapper());
    }

    public function testGlobTypeMapperExtend(): void
    {
        $container = new LazyContainer([
            FooType::class => static function () {
                return new FooType();
            },
            FooExtendType::class => static function () {
                return new FooExtendType();
            },
        ]);

        $typeGenerator = $this->getTypeGenerator();
        $inputTypeGenerator = $this->getInputTypeGenerator();

        $cache = new Psr16Cache(new ArrayAdapter());

        $mapper = new GlobTypeMapper($this->getClassFinder('TheCodingMachine\GraphQLite\Fixtures\Types'), $typeGenerator, $inputTypeGenerator, $this->getInputTypeUtils(), $container, new AnnotationReader(), new NamingStrategy(), $this->getTypeMapper(), $cache);

        $type = $mapper->mapClassToType(TestObject::class, null);

        $this->assertTrue($mapper->canExtendTypeForClass(TestObject::class, $type));
        $mapper->extendTypeForClass(TestObject::class, $type);
        $mapper->extendTypeForName('TestObject', $type);
        $this->assertTrue($mapper->canExtendTypeForName('TestObject', $type));
        $this->assertFalse($mapper->canExtendTypeForName('NotExists', $type));

        // Again to test cache
        $anotherMapperSameCache = new GlobTypeMapper($this->getClassFinder('TheCodingMachine\GraphQLite\Fixtures\Types'), $typeGenerator, $this->getInputTypeGenerator(), $this->getInputTypeUtils(), $container, new AnnotationReader(), new NamingStrategy(), $this->getTypeMapper(), $cache);
        $this->assertTrue($anotherMapperSameCache->canExtendTypeForClass(TestObject::class, $type));
        $this->assertTrue($anotherMapperSameCache->canExtendTypeForName('TestObject', $type));

        $this->expectException(CannotMapTypeException::class);
        $mapper->extendTypeForClass(stdClass::class, $type);
    }

    public function testEmptyGlobTypeMapper(): void
    {
        $container = new LazyContainer([]);

        $typeGenerator = $this->getTypeGenerator();
        $inputTypeGenerator = $this->getInputTypeGenerator();

        $cache = new Psr16Cache(new ArrayAdapter());

        $mapper = new GlobTypeMapper($this->getClassFinder('TheCodingMachine\GraphQLite\Fixtures\Integration\Controllers'), $typeGenerator, $inputTypeGenerator, $this->getInputTypeUtils(), $container, new AnnotationReader(), new NamingStrategy(), $this->getTypeMapper(), $cache);

        $this->assertSame([], $mapper->getSupportedClasses());
    }

    public function testGlobTypeMapperDecorate(): void
    {
        $container = new LazyContainer([
            FilterDecorator::class => static function () {
                return new FilterDecorator();
            },
        ]);

        $typeGenerator = $this->getTypeGenerator();
        $inputTypeGenerator = $this->getInputTypeGenerator();

        $cache = new Psr16Cache(new ArrayAdapter());

        $mapper = new GlobTypeMapper($this->getClassFinder('TheCodingMachine\GraphQLite\Fixtures\Integration\Types'), $typeGenerator, $inputTypeGenerator, $this->getInputTypeUtils(), $container, new AnnotationReader(), new NamingStrategy(), $this->getTypeMapper(), $cache);

        $inputType = new MockResolvableInputObjectType(['name' => 'FilterInput']);

        $mapper->decorateInputTypeForName('FilterInput', $inputType);

        $this->assertCount(3, $inputType->getDecorators());

        $this->expectException(CannotMapTypeException::class);
        $this->expectExceptionMessage('cannot decorate GraphQL input type "FilterInput" with type "NotExists". Check your TypeMapper configuration.');
        $mapper->decorateInputTypeForName('NotExists', $inputType);
    }

    public function testInvalidName(): void
    {
        $container = new LazyContainer([
            FooType::class => static function () {
                return new FooType();
            },
        ]);

        $typeGenerator = $this->getTypeGenerator();

        $mapper = new GlobTypeMapper($this->getClassFinder('TheCodingMachine\GraphQLite\Fixtures\Types'), $typeGenerator, $this->getInputTypeGenerator(), $this->getInputTypeUtils(), $container, new AnnotationReader(), new NamingStrategy(), $this->getTypeMapper(), new Psr16Cache(new ArrayAdapter()));

        $this->assertFalse($mapper->canExtendTypeForName('{}()/\\@:', new MutableObjectType(['name' => 'foo'])));
        $this->assertFalse($mapper->canDecorateInputTypeForName('{}()/\\@:', new MockResolvableInputObjectType(['name' => 'foo'])));
        $this->assertFalse($mapper->canMapNameToType('{}()/\\@:'));
    }

    public function testGlobTypeMapperExtendBadName(): void
    {
        $container = new LazyContainer([
            FooType::class => static function () {
                return new FooType();
            },
            FooExtendType::class => static function () {
                return new FooExtendType();
            },
            BadExtendType::class => static function () {
                return new BadExtendType();
            },
        ]);

        $typeGenerator = $this->getTypeGenerator();
        $inputTypeGenerator = $this->getInputTypeGenerator();

        $cache = new Psr16Cache(new ArrayAdapter());

        $mapper = new GlobTypeMapper($this->getClassFinder('TheCodingMachine\GraphQLite\Fixtures\BadExtendType'), $typeGenerator, $inputTypeGenerator, $this->getInputTypeUtils(), $container, new AnnotationReader(), new NamingStrategy(), $this->getTypeMapper(), $cache);

        $testObjectType = new MutableObjectType([
            'name'    => 'TestObject',
            'fields'  => [
                'test'   => Type::string(),
            ],
        ]);

        $this->expectException(CannotMapTypeException::class);
        $this->expectExceptionMessage('For @ExtendType(name="TestObjectInput") annotation declared in class "TheCodingMachine\GraphQLite\Fixtures\BadExtendType\BadExtendType", the pointed at GraphQL type cannot be extended. You can only target types extending the MutableObjectType (like types created with the @Type annotation).');
        $mapper->extendTypeForName('TestObject', $testObjectType);
    }

    public function testGlobTypeMapperExtendBadClass(): void
    {
        $container = new LazyContainer([
            FooType::class => static function () {
                return new FooType();
            },
            FooExtendType::class => static function () {
                return new FooExtendType();
            },
            BadExtendType2::class => static function () {
                return new BadExtendType2();
            },
        ]);

        $typeGenerator = $this->getTypeGenerator();
        $inputTypeGenerator = $this->getInputTypeGenerator();

        $cache = new Psr16Cache(new ArrayAdapter());

        $mapper = new GlobTypeMapper($this->getClassFinder('TheCodingMachine\GraphQLite\Fixtures\BadExtendType2'), $typeGenerator, $inputTypeGenerator, $this->getInputTypeUtils(), $container, new AnnotationReader(), new NamingStrategy(), $this->getTypeMapper(), $cache);

        $testObjectType = new MutableObjectType([
            'name'    => 'TestObject',
            'fields'  => [
                'test'   => Type::string(),
            ],
        ]);

        $this->expectException(CannotMapTypeException::class);
        $this->expectExceptionMessage('For @ExtendType(class="Exception") annotation declared in class "TheCodingMachine\GraphQLite\Fixtures\BadExtendType2\BadExtendType2", cannot map class "Exception" to a known GraphQL type. Check your TypeMapper configuration.');
        $mapper->extendTypeForName('TestObject', $testObjectType);
    }

    public function testNonInstantiableType(): void
    {
        $container = new LazyContainer([
            FooType::class => static function () {
                return new FooType();
            },
        ]);

        $typeGenerator = $this->getTypeGenerator();
        $inputTypeGenerator = $this->getInputTypeGenerator();

        $cache = new Psr16Cache(new ArrayAdapter());

        $mapper = new GlobTypeMapper($this->getClassFinder('TheCodingMachine\GraphQLite\Fixtures\NonInstantiableType'), $typeGenerator, $inputTypeGenerator, $this->getInputTypeUtils(), $container, new AnnotationReader(), new NamingStrategy(), $this->getTypeMapper(), $cache);

        $this->expectException(GraphQLRuntimeException::class);
        $this->expectExceptionMessage('Class "TheCodingMachine\GraphQLite\Fixtures\NonInstantiableType\AbstractFooType" annotated with @Type(class="TheCodingMachine\GraphQLite\Fixtures\TestObject") must be instantiable.');
        $mapper->mapClassToType(TestObject::class, null);
    }

    public function testNonInstantiableInput(): void
    {
        $container = new LazyContainer([]);

        $typeGenerator = $this->getTypeGenerator();
        $inputTypeGenerator = $this->getInputTypeGenerator();

        $cache = new Psr16Cache(new ArrayAdapter());
        $mapper = new GlobTypeMapper($this->getClassFinder('TheCodingMachine\GraphQLite\Fixtures\NonInstantiableInput'), $typeGenerator, $inputTypeGenerator, $this->getInputTypeUtils(), $container, new AnnotationReader(), new NamingStrategy(), $this->getTypeMapper(), $cache);

        $this->expectException(FailedResolvingInputType::class);
        $this->expectExceptionMessage("Class 'TheCodingMachine\GraphQLite\Fixtures\NonInstantiableInput\AbstractFoo' annotated with @Input must be instantiable.");
        $mapper->mapClassToInputType(AbstractFoo::class);
    }
}
