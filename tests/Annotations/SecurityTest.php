<?php

namespace TheCodingMachine\GraphQLite\Annotations;

use BadMethodCallException;
use PHPUnit\Framework\TestCase;

class SecurityTest extends TestCase
{

    public function testBadParams(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('The @Security annotation must be passed an expression. For instance: "@Security("is_granted(\'CAN_EDIT_STUFF\')")"');
        new Security([]);
    }

    public function testIncompatibleParams(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('A @Security annotation that has "failWith" attribute set cannot have a message or a statusCode attribute.');
        new Security(['expression'=>'foo', 'failWith'=>null, 'statusCode'=>500]);
    }
}
