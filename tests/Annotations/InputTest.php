<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Annotations;

use PHPUnit\Framework\TestCase;
use RuntimeException;

class InputTest extends TestCase
{
    public function testException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Empty class for #[Input] attribute. You MUST create the Input attribute object using the GraphQLite AnnotationReader');
        (new Input())->getClass();
    }
}
