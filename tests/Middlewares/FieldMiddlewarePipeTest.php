<?php

namespace TheCodingMachine\GraphQLite\Middlewares;

use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\Type;
use PHPUnit\Framework\TestCase;
use TheCodingMachine\GraphQLite\QueryFieldDescriptor;

class FieldMiddlewarePipeTest extends TestCase
{

    public function testHandle(): void
    {
        $finalHandler = new class implements FieldHandlerInterface {
            public function handle(QueryFieldDescriptor $fieldDescriptor): ?FieldDefinition
            {
                return new  FieldDefinition(['name'=>'foo', 'type'=>Type::string()]);
            }
        };

        $descriptor = new QueryFieldDescriptor(
            name: 'foo',
            type: Type::string(),
        );
        $middlewarePipe = new FieldMiddlewarePipe();

        $definition = $middlewarePipe->process($descriptor, $finalHandler);
        $this->assertSame('foo', $definition->name);

        $middlewarePipe->pipe(new class implements FieldMiddlewareInterface {
            public function process(QueryFieldDescriptor $queryFieldDescriptor, FieldHandlerInterface $fieldHandler): ?FieldDefinition
            {
                return new FieldDefinition(['name'=>'bar', 'type'=>Type::string()]);
            }
        });

        $descriptor = new QueryFieldDescriptor(
            name: 'bar',
            type: Type::string(),
        );

        $definition = $middlewarePipe->process($descriptor, $finalHandler);
        $this->assertSame('bar', $definition->name);
    }
}
