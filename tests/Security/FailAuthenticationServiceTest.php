<?php

namespace TheCodingMachine\GraphQLite\Security;

use PHPUnit\Framework\TestCase;

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
