<?php

namespace TheCodingMachine\GraphQLite\Tests\Containers;

use PHPUnit\Framework\TestCase;
use Psr\Container\NotFoundExceptionInterface;
use TheCodingMachine\GraphQLite\Containers\EmptyContainer;

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
