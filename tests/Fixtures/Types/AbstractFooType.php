<?php


namespace TheCodingMachine\GraphQL\Controllers\Fixtures\Types;

use TheCodingMachine\GraphQL\Controllers\AbstractAnnotatedObjectType;
use TheCodingMachine\GraphQL\Controllers\Annotations\Right;
use TheCodingMachine\GraphQL\Controllers\Annotations\SourceField;
use TheCodingMachine\GraphQL\Controllers\Annotations\Field;
use TheCodingMachine\GraphQL\Controllers\Annotations\Type;
use TheCodingMachine\GraphQL\Controllers\Fixtures\TestObject;
use TheCodingMachine\GraphQL\Controllers\Registry\Registry;
use TheCodingMachine\GraphQL\Controllers\Registry\RegistryInterface;
use Youshido\GraphQL\Config\Object\ObjectTypeConfig;
use Youshido\GraphQL\Type\Object\AbstractObjectType;

abstract class AbstractFooType /*extends AbstractAnnotatedObjectType*/
{
    /*public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry);
    }*/

    /**
     * @Field()
     * @param TestObject $test
     * @param string $param
     * @return string
     */
    public function customField(TestObject $test, string $param = 'foo'): string
    {
        return $test->getTest().$param;
    }
}
