<?php

namespace TheCodingMachine\GraphQLite\Annotations;

use BadMethodCallException;
use PHPUnit\Framework\TestCase;

class SourceFieldTest extends TestCase
{

    public function testExceptionInConstruct(): void
    {
        $this->expectException(BadMethodCallException::class);
        new SourceField([]);
    }

    public function testExceptionInConstruct2(): void
    {
        $this->expectException(BadMethodCallException::class);
        new SourceField(['name'=>'test', 'annotations'=>new Field()]);
    }

    public function testExceptionInConstruct3(): void
    {
        $this->expectException(BadMethodCallException::class);
        new SourceField(['name'=>'test', 'phpType'=>'string', 'outputType'=>'String!']);
    }
}
