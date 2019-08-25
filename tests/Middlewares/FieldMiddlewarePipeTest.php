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
                return FieldDefinition::create(['name'=>'foo', 'type'=>Type::string()]);
            }
        };

        $middlewarePipe = new FieldMiddlewarePipe();

        $definition = $middlewarePipe->process(new QueryFieldDescriptor(), $finalHandler);
        $this->assertSame('foo', $definition->name);

        $middlewarePipe->pipe(new class implements FieldMiddlewareInterface {
            public function process(QueryFieldDescriptor $queryFieldDescriptor, FieldHandlerInterface $fieldHandler): ?FieldDefinition
            {
                return FieldDefinition::create(['name'=>'bar', 'type'=>Type::string()]);
            }
        });

        $definition = $middlewarePipe->process(new QueryFieldDescriptor(), $finalHandler);
        $this->assertSame('bar', $definition->name);
    }
}
