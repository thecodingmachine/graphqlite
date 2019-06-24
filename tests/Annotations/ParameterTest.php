<?php

namespace TheCodingMachine\GraphQLite\Annotations;

use BadMethodCallException;
use PHPUnit\Framework\TestCase;

class ParameterTest extends TestCase
{

    public function testException()
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('The @Parameter annotation must be passed a target. For instance: "@Parameter(for="$input", inputType="MyInputType")"');
        new Parameter(['annotations'=>[]]);
    }
}
