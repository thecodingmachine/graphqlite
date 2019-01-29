<?php

namespace TheCodingMachine\GraphQLite\Hydrators;

use GraphQL\Type\Definition\InputObjectType;
use PHPUnit\Framework\TestCase;
use stdClass;
use TheCodingMachine\GraphQLite\Types\ResolvableInputObjectType;

class FactoryHydratorTest extends TestCase
{
    public function testHydratorNotFound()
    {
        $resolvableInputObjectType = $this->getMockBuilder(ResolvableInputObjectType::class)
            ->disableOriginalConstructor()
            ->setMethods(['resolve'])
            ->getMock();
        $resolvableInputObjectType->method('resolve')->willReturn(new stdClass());

        $badObjectType = new InputObjectType([
            'name' => 'foo'
        ]);

        $factoryHydrator = new FactoryHydrator();

        $this->assertTrue($factoryHydrator->canHydrate([], $resolvableInputObjectType));
        $this->assertFalse($factoryHydrator->canHydrate([], $badObjectType));

        $this->assertEquals(new stdClass(), $factoryHydrator->hydrate([], $resolvableInputObjectType));

        $this->expectException(CannotHydrateException::class);
        $factoryHydrator->hydrate([], $badObjectType);
    }
}
