<?php

namespace TheCodingMachine\GraphQL\Controllers\Registry;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use TheCodingMachine\GraphQL\Controllers\Fixtures\TestType;
use TheCodingMachine\GraphQL\Controllers\Security\AuthorizationServiceInterface;

class RegistryTest extends TestCase
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

    public function testFromContainer()
    {
        $registry = new Registry($this->getContainer());

        $this->assertTrue($registry->has('foo'));
        $this->assertFalse($registry->has('bar'));

        $this->assertSame('foo', $registry->get('foo'));
    }

    public function testInstantiate()
    {
        $registry = new Registry($this->getContainer());

        $this->assertTrue($registry->has(TestType::class));
        $type = $registry->get(TestType::class);
        $this->assertInstanceOf(TestType::class, $type);
        $this->assertSame($type, $registry->get(TestType::class));
        $this->assertTrue($registry->has(TestType::class));
    }

    public function testNotFound()
    {
        $registry = new Registry($this->getContainer());
        $this->expectException(NotFoundException::class);
        $registry->get('notfound');
    }

    public function testGetAuthorization()
    {
        $authorizationService = $this->createMock(AuthorizationServiceInterface::class);
        $registry = new Registry($this->getContainer(), $authorizationService);

        $this->assertSame($authorizationService, $registry->getAuthorizationService());

        $registry = new Registry($this->getContainer());

        $this->assertNull($registry->getAuthorizationService());
    }
}
