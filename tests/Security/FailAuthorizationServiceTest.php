<?php

namespace TheCodingMachine\GraphQL\Controllers\Security;

use PHPUnit\Framework\TestCase;

class FailAuthorizationServiceTest extends TestCase
{

    public function testIsAllowed()
    {
        $service = new FailAuthorizationService();
        $this->expectException(SecurityNotImplementedException::class);
        $service->isAllowed('foo');
    }
}
