<?php

namespace TheCodingMachine\GraphQLite\Tests\Security;

use PHPUnit\Framework\TestCase;
use TheCodingMachine\GraphQLite\Security\FailAuthorizationService;
use TheCodingMachine\GraphQLite\Security\SecurityNotImplementedException;

class FailAuthorizationServiceTest extends TestCase
{

    public function testIsAllowed(): void
    {
        $service = new FailAuthorizationService();
        $this->expectException(SecurityNotImplementedException::class);
        $service->isAllowed('foo');
    }
}
