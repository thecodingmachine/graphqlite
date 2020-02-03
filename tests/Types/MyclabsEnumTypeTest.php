<?php

namespace TheCodingMachine\GraphQLite\Types;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\ProductTypeEnum;

class MyclabsEnumTypeTest extends TestCase
{
    public function testException()
    {
        $enumType = new MyCLabsEnumType(ProductTypeEnum::class);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected a Myclabs Enum instance');
        $enumType->serialize('foo');
    }
}
