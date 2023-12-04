<?php

namespace TheCodingMachine\GraphQLite\Types;

use DateTime;
use GraphQL\Type\Definition\IntType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\StringType;
use Psr\Container\ContainerInterface;
use TheCodingMachine\GraphQLite\AbstractQueryProviderTest;
use TheCodingMachine\GraphQLite\Annotations\Exceptions\IncompatibleAnnotationsException;
use TheCodingMachine\GraphQLite\FailedResolvingInputType;
use TheCodingMachine\GraphQLite\Fixtures\Inputs\CircularInputA;
use TheCodingMachine\GraphQLite\Fixtures\Inputs\CircularInputB;
use TheCodingMachine\GraphQLite\Fixtures\Inputs\FooBar;
use TheCodingMachine\GraphQLite\Fixtures\Inputs\InputInterface;
use TheCodingMachine\GraphQLite\Fixtures\Inputs\InputWithSetter;
use TheCodingMachine\GraphQLite\Fixtures\Inputs\TestConstructorAndProperties;
use TheCodingMachine\GraphQLite\Fixtures\Inputs\TestConstructorPromotedProperties;
use TheCodingMachine\GraphQLite\Fixtures\Inputs\TestOnlyConstruct;
use TheCodingMachine\GraphQLite\Fixtures\Inputs\TypedFooBar;

class InputTypeTest extends AbstractQueryProviderTest
{
    public function testInputConfiguredCorrectly(): void
    {
        $input = new InputType(FooBar::class, 'FooBarInput', 'Test', false, $this->getFieldsBuilder());
        $input->freeze();

        $this->assertEquals('FooBarInput', $input->config['name']);
        $this->assertEquals('Test', $input->config['description']);

        $fields = $input->getFields();
        $this->assertCount(2, $fields);

        $this->assertEquals('foo', $fields['foo']->config['name']);
        $this->assertEquals('Foo description.', $fields['foo']->config['description']);
        $this->assertArrayNotHasKey('defaultValue', $fields['foo']->config);
        $this->assertInstanceOf(NonNull::class, $fields['foo']->getType());
        $this->assertInstanceOf(StringType::class, $fields['foo']->getType()->getWrappedType());

        $this->assertEquals('bar', $fields['bar']->config['name']);
        $this->assertEquals('Bar comment.', $fields['bar']->config['description']);
        $this->assertEquals('bar', $fields['bar']->config['defaultValue']);
        $this->assertNotInstanceOf(NonNull::class, $fields['bar']->getType());
    }

    public function testInputConfiguredCorrectlyWithTypedProperties(): void
    {
        $input = new InputType(TypedFooBar::class, 'TypedFooBarInput', 'Test', false, $this->getFieldsBuilder());
        $input->freeze();

        $fields = $input->getFields();
        $this->assertCount(2, $fields);

        $this->assertEquals('foo', $fields['foo']->config['name']);
        $this->assertArrayNotHasKey('defaultValue', $fields['foo']->config);
        $this->assertInstanceOf(NonNull::class, $fields['foo']->getType());
        $this->assertInstanceOf(StringType::class, $fields['foo']->getType()->getWrappedType());

        $this->assertEquals('bar', $fields['bar']->config['name']);
        $this->assertEquals(10, $fields['bar']->config['defaultValue']);
        $this->assertInstanceOf(IntType::class, $fields['bar']->getType());
    }

    public function testUpdateInputConfiguredCorrectly(): void
    {
        $input = new InputType(FooBar::class, 'FooBarUpdateInput', 'Test', true, $this->getFieldsBuilder());
        $input->freeze();

        $this->assertEquals('FooBarUpdateInput', $input->config['name']);
        $this->assertEquals('Test', $input->config['description']);

        $fields = $input->getFields();
        $this->assertCount(3, $fields);

        $this->assertEquals('foo', $fields['foo']->config['name']);
        $this->assertEquals('Foo description.', $fields['foo']->config['description']);
        $this->assertArrayNotHasKey('defaultValue', $fields['foo']->config);

        $this->assertEquals('bar', $fields['bar']->config['name']);
        $this->assertEquals('Bar comment.', $fields['bar']->config['description']);
        $this->assertArrayNotHasKey('defaultValue', $fields['foo']->config);

        $this->assertEquals('timestamp', $fields['timestamp']->config['name']);
        $this->assertEquals('', $fields['timestamp']->config['description']);
        $this->assertArrayNotHasKey('defaultValue', $fields['timestamp']->config);
    }

    public function testPassingInterfaceName(): void
    {
        $this->expectException(FailedResolvingInputType::class);
        $this->expectExceptionMessage("Class 'TheCodingMachine\GraphQLite\Fixtures\Inputs\InputInterface' annotated with @Input must be instantiable.");

        new InputType(InputInterface::class, 'TestInput', null, false, $this->getFieldsBuilder());
    }

    public function testInputCannotBeDecorator(): void
    {
        $this->expectException(FailedResolvingInputType::class);
        $this->expectExceptionMessage("Input type 'TheCodingMachine\GraphQLite\Fixtures\Inputs\FooBar' cannot be a decorator.");

        $input = new InputType(FooBar::class, 'FooBarInput', null, false, $this->getFieldsBuilder());
        $input->decorate(function () {});
    }

    public function testResolvesCorrectlyWithRequiredConstructParam(): void
    {
        $input = new InputType(FooBar::class, 'FooBarInput', null, false, $this->getFieldsBuilder());
        $input->freeze();
        $fields = $input->getFields();
        $args = ['foo' => 'Foo'];
        $resolveInfo = $this->createMock(ResolveInfo::class);
        $result = $input->resolve(null, $args, [], $resolveInfo);

        $this->assertSame([
            'foo' => 'Foo',
            'bar' => 'test',
            'date' => null,
        ], (array) $result);
    }

    public function testResolvesCorrectlyWithOnlyConstruct(): void
    {
        $input = new InputType(TestOnlyConstruct::class, 'TestOnlyConstructInput', null, false, $this->getFieldsBuilder());
        $input->freeze();
        $fields = $input->getFields();
        $args = [
            'baz' => false,
            'foo' => 'Foo',
            'bar' => 200,
        ];

        $resolveInfo = $this->createMock(ResolveInfo::class);

        /** @var TestOnlyConstruct $result */
        $result = $input->resolve(null, $args, [], $resolveInfo);

        $this->assertEquals(false, $result->getBaz());
        $this->assertEquals('Foo', $result->getFoo());
        $this->assertEquals(200, $result->getBar());
    }

    /**
     * @group PR-466
     */
    public function testResolvesCorrectlyWithConstructorAndProperties(): void
    {
        $input = new InputType(
            TestConstructorAndProperties::class,
            'TestConstructorAndPropertiesInput',
            null,
            false,
            $this->getFieldsBuilder(),
        );
        $input->freeze();
        $fields = $input->getFields();

        $date = "2022-05-02T04:42:30Z";

        $args = [
            'date' => $date,
            'foo' => 'Foo',
            'bar' => 200,
        ];

        $resolveInfo = $this->createMock(ResolveInfo::class);

        /** @var TestConstructorAndProperties $result */
        $result = $input->resolve(null, $args, [], $resolveInfo);

        $this->assertEquals(new DateTime("2022-05-02T04:42:30Z"), $result->getDate());
        $this->assertEquals('Foo', $result->getFoo());
        $this->assertEquals(200, $result->getBar());
    }

    public function testResolvesCorrectlyWithConstructorPromotedProperties(): void
    {
        $input = new InputType(
            TestConstructorPromotedProperties::class,
            'TestConstructorPromotedPropertiesInput',
            null,
            false,
            $this->getFieldsBuilder(),
        );
        $input->freeze();
        $fields = $input->getFields();

        $date = "2022-05-02T04:42:30Z";

        $args = [
            'date' => $date,
            'foo' => 'Foo',
            'bar' => 200,
        ];

        $resolveInfo = $this->createMock(ResolveInfo::class);

        /** @var TestConstructorPromotedProperties $result */
        $result = $input->resolve(null, $args, [], $resolveInfo);

        $this->assertEquals(new DateTime("2022-05-02T04:42:30Z"), $result->getDate());
        $this->assertEquals('Foo', $result->foo);
        $this->assertEquals(200, $result->getBar());
    }

    public function testFailsResolvingFieldWithoutRequiredConstructParam(): void
    {
        $input = new InputType(FooBar::class, 'FooBarInput', null, false, $this->getFieldsBuilder());
        $input->freeze();
        $fields = $input->getFields();
        $args = ['bar' => 'Bar'];
        $resolveInfo = $this->createMock(ResolveInfo::class);

        $this->expectException(FailedResolvingInputType::class);
        $this->expectExceptionMessage("TheCodingMachine\GraphQLite\Fixtures\Inputs\FooBar::__construct(): Argument #1 (\$foo) not passed. It should be mapped as required field.");

        $input->resolve(null, $args, [], $resolveInfo);
    }

    public function testSimpleSetterAnnotated(): void
    {
        $input = new InputType(InputWithSetter::class, 'InputWithSetterInput', null, false, $this->getFieldsBuilder());
        $input->freeze();
        $fields = $input->getFields();
        $args = [
            'foo' => 'Foo',
            'bar' => 200,
        ];

        $resolveInfo = $this->createMock(ResolveInfo::class);
        /** @var TestOnlyConstruct $result */
        $result = $input->resolve(null, $args, [], $resolveInfo);

        $this->assertEquals('Foo', $result->getFoo());
        $this->assertEquals(200, $result->getBar());
    }

    public function testForceInputTypeWithUpdate(): void
    {
        $input = new InputType(InputWithSetter::class, 'ForcedTypeInput', null, true, $this->getFieldsBuilder());
        $input->freeze();

        $fields = $input->getFields();
        $this->assertInstanceOf(NonNull::class, $fields['bar']->getType());
    }

}
