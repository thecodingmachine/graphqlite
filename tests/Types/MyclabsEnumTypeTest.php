<?php

namespace TheCodingMachine\GraphQLite\Types;

use InvalidArgumentException;
use MyCLabs\Enum\Enum;
use PHPUnit\Framework\TestCase;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\ProductTypeEnum;

class MyclabsEnumTypeTest extends TestCase
{
    public function testException()
    {
        $enumType = new MyCLabsEnumType(ProductTypeEnum::class, 'foo');
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected a Myclabs Enum instance');
        $enumType->serialize('foo');
    }

    public function testConversion()
    {
        $enumType = new MyCLabsEnumType(ProductTypeEnum::class, 'foo');
        /** @var Enum $result */
        $result = $enumType->serialize('food');
        $this->assertEquals('FOOD', $result);
    }
}
