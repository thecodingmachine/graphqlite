<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Containers;

use TheCodingMachine\GraphQLite\AbstractQueryProviderTest;
use TheCodingMachine\GraphQLite\Fixtures\TestType;

final class LazyContainerTest extends AbstractQueryProviderTest
{
    private function getContainer(): LazyContainer
    {
        return new LazyContainer(entries: [
            'foo' => static fn () => 'bar',
        ]);
    }

    public function testFromContainer(): void
    {
        $container = $this->buildAutoWiringContainer($this->getContainer());

        $this->assertTrue($container->has('foo'));
        $this->assertFalse($container->has('bar'));

        $this->assertSame('bar', $container->get('foo'));
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
