<?php

namespace TheCodingMachine\GraphQLite\Containers;

use PHPUnit\Framework\TestCase;
use Psr\Container\NotFoundExceptionInterface;

class EmptyContainerTest extends TestCase
{
    public function testContainer(): void
    {
        $container = new EmptyContainer();
        $this->assertFalse($container->has('foo'));
        $this->expectException(NotFoundExceptionInterface::class);
        $container->get('foo');
    }
}
