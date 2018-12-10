<?php

namespace TheCodingMachine\GraphQL\Controllers\Types;

use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\StringType;
use PHPUnit\Framework\TestCase;
use TheCodingMachine\GraphQL\Controllers\AbstractQueryProviderTest;
use TheCodingMachine\GraphQL\Controllers\Fixtures\TestObject;
use TheCodingMachine\GraphQL\Controllers\Fixtures\TestObject2;

class UnionTypeTest extends AbstractQueryProviderTest
{
    public function testConstructor()
    {
        $unionType = new UnionType([$this->getTestObjectType(), $this->getTestObjectType2()], $this->getTypeMapper());
        $resolveInfo = $this->getMockBuilder(ResolveInfo::class)->disableOriginalConstructor()->getMock();
        $type = $unionType->resolveType(new TestObject('foo'), null, $resolveInfo);
        $this->assertSame($this->getTestObjectType(), $type);
        $type = $unionType->resolveType(new TestObject2('foo'), null, $resolveInfo);
        $this->assertSame($this->getTestObjectType2(), $type);
    }

    public function testException()
    {
        $unionType = new UnionType([$this->getTestObjectType(), $this->getTestObjectType2()], $this->getTypeMapper());
        $this->expectException(\InvalidArgumentException::class);
        $resolveInfo = $this->getMockBuilder(ResolveInfo::class)->disableOriginalConstructor()->getMock();
        $unionType->resolveType('foo', null, $resolveInfo);
    }

    public function testException2()
    {
        $this->expectException(\InvalidArgumentException::class);
        new UnionType([new StringType()], $this->getTypeMapper());
    }
}
