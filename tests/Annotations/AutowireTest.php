<?php

namespace TheCodingMachine\GraphQLite\Tests\Annotations;

use BadMethodCallException;
use PHPUnit\Framework\TestCase;
use TheCodingMachine\GraphQLite\Annotations\Autowire;

class AutowireTest extends TestCase
{

    public function testException(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('The #[Autowire] attribute must be passed a target. For instance: "#[Autowire(for: "$myService")]"');
        (new Autowire([]))->getTarget();
    }
}
