<?php

namespace TheCodingMachine\GraphQLite\Middlewares;

use GraphQL\Type\Definition\Type;
use PHPUnit\Framework\TestCase;
use TheCodingMachine\GraphQLite\InputField;
use TheCodingMachine\GraphQLite\InputFieldDescriptor;

class InputFieldMiddlewarePipeTest extends TestCase
{
    public function testHandle(): void
    {
        $finalHandler = new class () implements InputFieldHandlerInterface {
            public function handle(InputFieldDescriptor $inputFieldDescriptor): ?InputField
            {
                return InputField::fromFieldDescriptor($inputFieldDescriptor);
            }
        };

        $middlewarePipe = new InputFieldMiddlewarePipe();
        $inputFieldDescriptor = new InputFieldDescriptor();
        $inputFieldDescriptor->setCallable(static function () {
            return null;
        });
        $inputFieldDescriptor->setName('foo');
        $inputFieldDescriptor->setType(Type::string());
        $definition = $middlewarePipe->process($inputFieldDescriptor, $finalHandler);
        $this->assertSame('foo', $definition->name);

        $middlewarePipe->pipe(new class () implements InputFieldMiddlewareInterface {
            public function process(InputFieldDescriptor $inputFieldDescriptor, InputFieldHandlerInterface $inputFieldHandler): ?InputField
            {
                $inputFieldDescriptor->setName('bar');

                return InputField::fromFieldDescriptor($inputFieldDescriptor);
            }
        });

        $definition = $middlewarePipe->process($inputFieldDescriptor, $finalHandler);
        $this->assertSame('bar', $definition->name);
    }
}
