<?php

namespace TheCodingMachine\GraphQLite\Containers;

use GraphQL\Type\Definition\ObjectType;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use TheCodingMachine\GraphQLite\AbstractQueryProviderTest;
use TheCodingMachine\GraphQLite\Fixtures\TestType;
use TheCodingMachine\GraphQLite\Security\AuthorizationServiceInterface;

class BasicAutoWiringContainerTest extends AbstractQueryProviderTest
{
    private function getContainer(): ContainerInterface
    {
        return new class implements ContainerInterface {
            public function get($id)
            {
                return 'foo';
            }

            public function has($id)
            {
                return $id === 'foo';
            }
        };
    }

    public function testFromContainer(): void
    {
        $container = $this->buildAutoWiringContainer($this->getContainer());

        $this->assertTrue($container->has('foo'));
        $this->assertFalse($container->has('bar'));

        $this->assertSame('foo', $container->get('foo'));
    }

    public function testInstantiate(): void
    {
        $container = $this->buildAutoWiringContainer($this->getContainer());

        $this->assertTrue($container->has(TestType::class));
        $type = $container->get(TestType::class);
        $this->assertInstanceOf(TestType::class, $type);
        $this->assertSame($type, $container->get(TestType::class));
        $this->assertTrue($container->has(TestType::class));
    }

    public function testNotFound(): void
    {
        $container = $this->buildAutoWiringContainer($this->getContainer());
        $this->expectException(NotFoundException::class);
        $container->get('notfound');
    }
}
