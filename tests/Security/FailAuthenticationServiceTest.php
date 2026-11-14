<?php

namespace TheCodingMachine\GraphQLite\Tests\Security;

use PHPUnit\Framework\TestCase;
use TheCodingMachine\GraphQLite\Security\FailAuthenticationService;
use TheCodingMachine\GraphQLite\Security\SecurityNotImplementedException;

class FailAuthenticationServiceTest extends TestCase
{

    public function testIsAllowed(): void
    {
        $service = new FailAuthenticationService();
        $this->expectException(SecurityNotImplementedException::class);
        $service->isLogged();
    }

    public function testGetUser(): void
    {
        $service = new FailAuthenticationService();
        $this->expectException(SecurityNotImplementedException::class);
        $service->getUser();
    }
}
