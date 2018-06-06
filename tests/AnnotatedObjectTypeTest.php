<?php


namespace TheCodingMachine\GraphQL\Controllers;


use TheCodingMachine\GraphQL\Controllers\Fixtures\TestObject;
use TheCodingMachine\GraphQL\Controllers\Fixtures\TestType;
use Youshido\GraphQL\Execution\ResolveInfo;

class AnnotatedObjectTypeTest extends AbstractQueryProviderTest
{
    public function testBuild()
    {
        $type = new TestType($this->getRegistry());
        $obj = new TestObject('bar');
        $mockResolveInfo = $this->createMock(ResolveInfo::class);

        $result = $type->getField('customField')->resolve($obj, ['param' => 'foo'], $mockResolveInfo);

        $this->assertSame('barfoo', $result);
    }
}
