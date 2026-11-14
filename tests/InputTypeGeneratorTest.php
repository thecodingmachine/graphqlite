<?php

namespace TheCodingMachine\GraphQLite\Tests;

use TheCodingMachine\GraphQLite\InputTypeGenerator;


use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class InputTypeGeneratorTest extends TestCase
{

    public function testCanBeInstantiatedWithoutParameter(): void
    {
        $method = new ReflectionMethod(__CLASS__, 'stub');
        
        $this->assertTrue(InputTypeGenerator::canBeInstantiatedWithoutParameter($method, true));
    }

    private function stub(int $a, ?string $b, ?string $c, string $d = '') {
        // ...
    }
}
