<?php

namespace TheCodingMachine\GraphQLite\Annotations;

use BadMethodCallException;
use PHPUnit\Framework\TestCase;
use stdClass;
use TypeError;

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

    public function testBadParams2(): void
    {
        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('"TheCodingMachine\GraphQLite\Annotations\Security::__construct": Argument $data is expected to be a string or array, got "object".');
        new Security(new stdClass());
    }
}
