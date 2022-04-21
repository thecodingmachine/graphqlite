<?php

namespace TheCodingMachine\GraphQLite\Middlewares;

use GraphQL\Type\Definition\InputObjectField;
use GraphQL\Type\Definition\Type;
use PHPUnit\Framework\TestCase;
use TheCodingMachine\GraphQLite\InputFieldDescriptor;

class InputFieldMiddlewarePipeTest extends TestCase
{

    public function testHandle(): void
    {
        $finalHandler = new class implements InputFieldHandlerInterface {
            public function handle(InputFieldDescriptor $inputFieldDescriptor): ?InputObjectField
            {
                return new InputObjectField(['name'=>'foo', 'type'=>Type::string()]);
            }
        };

        $middlewarePipe = new InputFieldMiddlewarePipe();

        $definition = $middlewarePipe->process(new InputFieldDescriptor(), $finalHandler);
        $this->assertSame('foo', $definition->name);

        $middlewarePipe->pipe(new class implements InputFieldMiddlewareInterface {
            public function process(InputFieldDescriptor $inputFieldDescriptor, InputFieldHandlerInterface $inputFieldHandler): ?InputObjectField
            {
                return new InputObjectField(['name'=>'bar', 'type'=>Type::string()]);
            }
        });

        $definition = $middlewarePipe->process(new InputFieldDescriptor(), $finalHandler);
        $this->assertSame('bar', $definition->name);
    }
}
