<?php


namespace TheCodingMachine\GraphQL\Controllers\Fixtures;

use TheCodingMachine\GraphQL\Controllers\Registry\Registry;
use Youshido\GraphQL\Config\Object\ObjectTypeConfig;
use Youshido\GraphQL\Type\Object\AbstractObjectType;

class TestType extends AbstractObjectType
{
    public function __construct(Registry $registry)
    {
        parent::__construct([]);
    }

    /**
     * @param ObjectTypeConfig $config
     */
    public function build($config)
    {
    }
}
