<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Containers;

use Psr\Container\ContainerInterface;
use TheCodingMachine\GraphQLite\AbstractQueryProvider;
use TheCodingMachine\GraphQLite\Fixtures\TestType;

class BasicAutoWiringContainerTest extends AbstractQueryProvider
{
    private function getContainer(): ContainerInterface
    {
        return new class implements ContainerInterface {
            public function get($id):string
            {
                return 'foo';
            }

            public function has($id): bool
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
