<?php

namespace TheCodingMachine\GraphQLite\Tests;

use PHPUnit\Framework\TestCase;
use TheCodingMachine\GraphQLite\PrefetchBuffer;
use stdClass;

class PrefetchBufferTest extends TestCase
{

    public function testRegister(): void
    {
        $buffer = new PrefetchBuffer();

        $object1 = new stdClass();
        $object2 = new stdClass();
        $object3 = new stdClass();

        $buffer->register($object1, ['int'=>42]);
        $buffer->register($object2, ['int'=>24]);
        $buffer->register($object3, ['int'=>42]);

        $this->assertSame([$object1, $object3], $buffer->getObjectsByArguments(['int'=>42]));
        $this->assertSame([$object2], $buffer->getObjectsByArguments(['int'=>24]));

        $buffer->purge(['int'=>42]);
        $this->assertSame([], $buffer->getObjectsByArguments(['int'=>42]));
    }

    public function testResultsPerArguments(): void
    {
        $buffer = new PrefetchBuffer();

        $source = new stdClass();

        $buffer->storeResult($source, 'foo', ['prefix' => 'foo']);
        $buffer->storeResult($source, null, ['prefix' => 'bar']);

        $this->assertTrue($buffer->hasResult($source, ['prefix' => 'foo']));
        $this->assertTrue($buffer->hasResult($source, ['prefix' => 'bar']));
        $this->assertFalse($buffer->hasResult($source, ['prefix' => 'baz']));
        $this->assertFalse($buffer->hasResult($source));
        $this->assertSame('foo', $buffer->getResult($source, ['prefix' => 'foo']));
        $this->assertNull($buffer->getResult($source, ['prefix' => 'bar']));

        $buffer->purgeResult($source, ['prefix' => 'foo']);
        $this->assertFalse($buffer->hasResult($source, ['prefix' => 'foo']));
        $this->assertTrue($buffer->hasResult($source, ['prefix' => 'bar']));

        $buffer->purgeResult($source, ['prefix' => 'bar']);
        $this->assertFalse($buffer->hasResult($source, ['prefix' => 'bar']));
    }
}
