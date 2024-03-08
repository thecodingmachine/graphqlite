<?php

namespace TheCodingMachine\GraphQLite\Middlewares;

use GraphQL\Type\Definition\Type;
use PHPUnit\Framework\TestCase;
use TheCodingMachine\GraphQLite\InputFieldDescriptor;
use TheCodingMachine\GraphQLite\InputField;

class InputFieldMiddlewarePipeTest extends TestCase
{

    public function testHandle(): void
    {
        $finalHandler = new class implements InputFieldHandlerInterface {
            public function handle(InputFieldDescriptor $inputFieldDescriptor): ?InputField
            {
                return InputField::fromFieldDescriptor($inputFieldDescriptor);
            }
        };

        $resolver = static function (){
            return null;
        };
        $middlewarePipe = new InputFieldMiddlewarePipe();
        $inputFieldDescriptor = new InputFieldDescriptor(
            name: 'foo',
            type: Type::string(),
            resolver: $resolver,
            originalResolver: new ServiceResolver($resolver),
        );
        $definition = $middlewarePipe->process($inputFieldDescriptor, $finalHandler);
        $this->assertSame('foo', $definition->name);

        $middlewarePipe->pipe(new class implements InputFieldMiddlewareInterface {
            public function process(InputFieldDescriptor $inputFieldDescriptor, InputFieldHandlerInterface $inputFieldHandler): ?InputField
            {
                return InputField::fromFieldDescriptor(
                    $inputFieldDescriptor->withName("bar")
                );
            }
        });

        $definition = $middlewarePipe->process($inputFieldDescriptor, $finalHandler);
        $this->assertSame('bar', $definition->name);
    }
}
