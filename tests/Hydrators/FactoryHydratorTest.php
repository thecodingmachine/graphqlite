<?php

namespace TheCodingMachine\GraphQL\Controllers\Hydrators;

use GraphQL\Type\Definition\InputObjectType;
use PHPUnit\Framework\TestCase;

class FactoryHydratorTest extends TestCase
{
    public function testHydratorNotFound()
    {
        $factoryHydrator = new FactoryHydrator();
        $this->expectException(CannotHydrateException::class);
        $factoryHydrator->hydrate([], new InputObjectType([
            'name' => 'foo'
        ]));
    }
}
