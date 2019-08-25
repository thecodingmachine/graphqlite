<?php

namespace TheCodingMachine\GraphQLite\Types;

use GraphQL\Type\Definition\IDType;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use TheCodingMachine\GraphQLite\Mappers\CannotMapTypeException;

class TypeResolverTest extends TestCase
{
    public function testException(): void
    {
        $typeResolver = new TypeResolver();
        $this->expectException(RuntimeException::class);
        $typeResolver->mapNameToType('ID');
    }

    public function testMapNameToType(): void
    {
        $typeResolver = new TypeResolver();
        $schema = new Schema([]);
        $typeResolver->registerSchema($schema);
        $this->assertInstanceOf(IDType::class, $typeResolver->mapNameToType('ID'));

        $this->expectException(CannotMapTypeException::class);
        $typeResolver->mapNameToType('NotExists');
    }

    private function getTestSchema(): Schema
    {
        return new Schema([
            'query' => new ObjectType([
                'name' => 'Query',
                'fields' => [
                    'hello' => [
                        'type' => Type::string(),
                        'resolve' => function() {
                            return 'Hello World!';
                        }
                    ],
                ]
            ]),
            'typeLoader' => function($name) {
                if ($name === 'MyInput') {
                    return new InputObjectType([
                        'name' => 'MyInput'
                    ]);
                };
                if ($name === 'MyOutput') {
                    return new ObjectType([
                        'name' => 'MyOutput'
                    ]);
                };
            }
        ]);
    }

    public function testMapNameToOuputTypeException(): void
    {
        $typeResolver = new TypeResolver();
        $schema = $this->getTestSchema();
        $typeResolver->registerSchema($schema);

        $this->expectException(CannotMapTypeException::class);
        $typeResolver->mapNameToOutputType('MyInput');
    }

    public function testMapNameToInputTypeException(): void
    {
        $typeResolver = new TypeResolver();
        $schema = $this->getTestSchema();
        $typeResolver->registerSchema($schema);

        $this->expectException(CannotMapTypeException::class);
        $typeResolver->mapNameToInputType('MyOutput');
    }
}
