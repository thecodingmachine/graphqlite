<?php

namespace TheCodingMachine\GraphQLite\Annotations;

use PHPUnit\Framework\TestCase;
use RuntimeException;

class TypeTest extends TestCase
{
    public function testException()
    {
        $type = new Type([]);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Empty class for @Type annotation. You MUST create the Type annotation object using the GraphQLite AnnotationReader');
        $type->getClass();
    }
}
