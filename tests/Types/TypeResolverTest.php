<?php

namespace TheCodingMachine\GraphQLite\Types;

use GraphQL\Type\Definition\IDType;
use GraphQL\Type\Schema;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use TheCodingMachine\GraphQLite\Mappers\CannotMapTypeException;

class TypeResolverTest extends TestCase
{
    public function testException()
    {
        $typeResolver = new TypeResolver();
        $this->expectException(RuntimeException::class);
        $typeResolver->mapNameToType('ID');
    }

    public function testMapNameToType()
    {
        $typeResolver = new TypeResolver();
        $schema = new Schema([]);
        $typeResolver->registerSchema($schema);
        $this->assertInstanceOf(IDType::class, $typeResolver->mapNameToType('ID'));

        $this->expectException(CannotMapTypeException::class);
        $typeResolver->mapNameToType('NotExists');
    }
}
