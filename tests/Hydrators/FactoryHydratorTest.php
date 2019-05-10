<?php

namespace TheCodingMachine\GraphQLite\Hydrators;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use PHPUnit\Framework\TestCase;
use stdClass;
use TheCodingMachine\GraphQLite\Types\ResolvableMutableInputObjectType;

class FactoryHydratorTest extends TestCase
{
    public function testHydratorNotFound()
    {
        $resolvableInputObjectType = $this->getMockBuilder(ResolvableMutableInputObjectType::class)
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

        $this->assertEquals(new stdClass(), $factoryHydrator->hydrate(null, [], null, $this->createMock(ResolveInfo::class), $resolvableInputObjectType));

        $this->expectException(CannotHydrateException::class);
        $factoryHydrator->hydrate(null, [], null, $this->createMock(ResolveInfo::class), $badObjectType);
    }
}
