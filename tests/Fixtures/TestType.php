<?php


namespace TheCodingMachine\GraphQL\Controllers\Fixtures;

use TheCodingMachine\GraphQL\Controllers\AbstractAnnotatedObjectType;
use TheCodingMachine\GraphQL\Controllers\Annotations\Field;
use TheCodingMachine\GraphQL\Controllers\Registry\Registry;
use TheCodingMachine\GraphQL\Controllers\Registry\RegistryInterface;
use Youshido\GraphQL\Config\Object\ObjectTypeConfig;
use Youshido\GraphQL\Type\Object\AbstractObjectType;

class TestType extends AbstractAnnotatedObjectType
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry);
    }

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
