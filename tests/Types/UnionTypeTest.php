<?php

namespace TheCodingMachine\GraphQLite\Types;

use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\StringType;
use PHPUnit\Framework\TestCase;
use TheCodingMachine\GraphQLite\AbstractQueryProvider;
use TheCodingMachine\GraphQLite\Fixtures\TestObject;
use TheCodingMachine\GraphQLite\Fixtures\TestObject2;
use TheCodingMachine\GraphQLite\NamingStrategy;

class UnionTypeTest extends AbstractQueryProvider
{
    public function testConstructor(): void
    {
        $unionType = new UnionType([$this->getTestObjectType(), $this->getTestObjectType2()], $this->getTypeMapper(), new NamingStrategy());
        $resolveInfo = $this->getMockBuilder(ResolveInfo::class)->disableOriginalConstructor()->getMock();
        $type = $unionType->resolveType(new TestObject('foo'), null, $resolveInfo);
        $this->assertSame($this->getTestObjectType(), $type);
        $type = $unionType->resolveType(new TestObject2('foo'), null, $resolveInfo);
        $this->assertSame($this->getTestObjectType2(), $type);
    }

    public function testException(): void
    {
        $unionType = new UnionType([$this->getTestObjectType(), $this->getTestObjectType2()], $this->getTypeMapper(), new NamingStrategy());
        $this->expectException(\InvalidArgumentException::class);
        $resolveInfo = $this->getMockBuilder(ResolveInfo::class)->disableOriginalConstructor()->getMock();
        $unionType->resolveType('foo', null, $resolveInfo);
    }

    public function testException2(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new UnionType([new StringType()], $this->getTypeMapper(), new NamingStrategy());
    }
}
