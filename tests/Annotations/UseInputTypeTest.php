<?php

namespace TheCodingMachine\GraphQLite\Annotations;

use BadMethodCallException;
use PHPUnit\Framework\TestCase;

class UseInputTypeTest extends TestCase
{

    public function testException(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('The @UseInputType annotation must be passed an input type. For instance: "@UseInputType(for="$input", inputType="MyInputType")" in PHP 7+ or #[UseInputType("MyInputType")] in PHP 8+');
        new UseInputType([]);
    }

    public function testException2(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('The @UseInputType annotation must be passed a target and an input type. For instance: "@UseInputType(for="$input", inputType="MyInputType")" in PHP 7+ or #[UseInputType("MyInputType")] in PHP 8+');
        (new UseInputType(['inputType' => 'foo']))->getTarget();
    }
}
