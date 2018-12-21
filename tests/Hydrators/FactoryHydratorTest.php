<?php

namespace TheCodingMachine\GraphQL\Controllers\Hydrators;

use GraphQL\Type\Definition\InputObjectType;
use PHPUnit\Framework\TestCase;
use TheCodingMachine\GraphQL\Controllers\GraphQLException;

class FactoryHydratorTest extends TestCase
{
    public function testHydratorNotFound()
    {
        $factoryHydrator = new FactoryHydrator();
        $this->expectException(GraphQLException::class);
        $factoryHydrator->hydrate([], new InputObjectType([
            'name' => 'foo'
        ]));
    }
}
