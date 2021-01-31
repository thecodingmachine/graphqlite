<?php

namespace TheCodingMachine\GraphQLite\Types;

use GraphQL\Type\Definition\ResolveInfo;
use TheCodingMachine\GraphQLite\AbstractQueryProviderTest;
use TheCodingMachine\GraphQLite\FailedResolvingInputType;
use TheCodingMachine\GraphQLite\Fixtures\Inputs\FooBar;
use TheCodingMachine\GraphQLite\Fixtures\Inputs\InputInterface;

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

        $this->assertEquals('bar', $fields['bar']->config['name']);
        $this->assertEquals('Bar comment.', $fields['bar']->config['description']);
        $this->assertEquals('bar', $fields['bar']->config['defaultValue']);
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

        $args = ['foo' => 'Foo'];
        $resolveInfo = $this->createMock(ResolveInfo::class);
        $result = $input->resolve(null, $args, [], $resolveInfo);

        $this->assertSame([
            'foo' => 'Foo',
            'bar' => 'test',
        ], (array) $result);
    }

    public function testFailsResolvingFieldWithoutRequiredConstructParam(): void
    {
        $input = new InputType(FooBar::class, 'FooBarInput', null, false, $this->getFieldsBuilder());

        $args = ['bar' => 'Bar'];
        $resolveInfo = $this->createMock(ResolveInfo::class);

        $this->expectException(FailedResolvingInputType::class);
        $this->expectExceptionMessage("Parameter 'foo' is missing for class 'TheCodingMachine\GraphQLite\Fixtures\Inputs\FooBar' constructor. It should be mapped as required field.");

        $input->resolve(null, $args, [], $resolveInfo);
    }
}
