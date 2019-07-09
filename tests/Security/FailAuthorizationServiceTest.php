<?php

namespace TheCodingMachine\GraphQLite\Security;

use PHPUnit\Framework\TestCase;

class FailAuthorizationServiceTest extends TestCase
{

    public function testIsAllowed(): void
    {
        $service = new FailAuthorizationService();
        $this->expectException(SecurityNotImplementedException::class);
        $service->isAllowed('foo');
    }
}
