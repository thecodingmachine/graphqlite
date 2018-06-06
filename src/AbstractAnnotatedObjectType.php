<?php


namespace TheCodingMachine\GraphQL\Controllers;


use TheCodingMachine\GraphQL\Controllers\Registry\RegistryInterface;
use Youshido\GraphQL\Config\Object\ObjectTypeConfig;
use Youshido\GraphQL\Type\Object\AbstractObjectType;

abstract class AbstractAnnotatedObjectType extends AbstractObjectType
{
    /**
     * @var RegistryInterface
     */
    private $registry;

    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
        parent::__construct([]);
    }

    /**
     * @param ObjectTypeConfig $config
     */
    public function build($config)
    {
        $fieldProvider = new ControllerQueryProvider($this, $this->registry);
        $fields = $fieldProvider->getFields();
        $this->addFields($fields);
    }
}