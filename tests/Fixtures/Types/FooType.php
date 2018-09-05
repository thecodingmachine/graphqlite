<?php


namespace TheCodingMachine\GraphQL\Controllers\Fixtures\Types;

use TheCodingMachine\GraphQL\Controllers\AbstractAnnotatedObjectType;
use TheCodingMachine\GraphQL\Controllers\Annotations\Right;
use TheCodingMachine\GraphQL\Controllers\Annotations\SourceField;
use TheCodingMachine\GraphQL\Controllers\Annotations\Field;
use TheCodingMachine\GraphQL\Controllers\Annotations\Type;
use TheCodingMachine\GraphQL\Controllers\Registry\Registry;
use TheCodingMachine\GraphQL\Controllers\Registry\RegistryInterface;
use Youshido\GraphQL\Config\Object\ObjectTypeConfig;
use Youshido\GraphQL\Type\Object\AbstractObjectType;

/**
 * @Type(class=TheCodingMachine\GraphQL\Controllers\Fixtures\TestObject::class)
 * @SourceField(name="test")
 * @SourceField(name="testBool", logged=true)
 * @SourceField(name="testRight", right=@Right(name="FOOBAR"))
 */
class FooType extends AbstractFooType
{
}
