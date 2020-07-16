<?php

namespace TheCodingMachine\GraphQLite;

use PHPUnit\Framework\TestCase;
use stdClass;

class PrefetchBufferTest extends TestCase
{

    public function testRegister(): void
    {
        $buffer = new PrefetchBuffer();

        $object1 = new stdClass();
        $object2 = new stdClass();
        $object3 = new stdClass();

        $buffer->register($object1, 'test', ['int'=>42]);
        $buffer->register($object2, 'test', ['int'=>24]);
        $buffer->register($object3, 'test', ['int'=>42]);

        $this->assertSame([$object1, $object3], $buffer->getObjectsByArguments('test', ['int'=>42]));
        $this->assertSame([$object2], $buffer->getObjectsByArguments('test', ['int'=>24]));

        $buffer->purge('test', ['int'=>42]);
        $this->assertSame([], $buffer->getObjectsByArguments('test', ['int'=>42]));
    }
}
