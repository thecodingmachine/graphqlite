<?php

namespace TheCodingMachine\GraphQLite\Utils;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Simple\ArrayCache;

class NamespacedCacheTest extends TestCase
{
    public function testCache()
    {
        $cache = new NamespacedCache(new ArrayCache());

        $cache->set('foo', 'bar');
        $this->assertSame('bar', $cache->get('foo'));
        $cache->delete('foo');
        $this->assertSame('baz', $cache->get('foo', 'baz'));

        $cache->setMultiple(['foo' => 'bar']);
        $this->assertSame(['foo' => 'bar'], $cache->getMultiple(['foo']));
        $this->assertTrue($cache->has('foo'));
        $cache->deleteMultiple(['foo']);
        $this->assertFalse($cache->has('foo'));

        $cache->set('foo', 'bar');
        $cache->clear();
        $this->assertFalse($cache->has('foo'));
    }
}
