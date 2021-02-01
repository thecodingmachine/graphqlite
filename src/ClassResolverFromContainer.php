<?php


namespace TheCodingMachine\GraphQLite;


use Psr\Container\ContainerInterface;

class ClassResolverFromContainer implements ClassResolver
{
    /** @var ContainerInterface */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(array $classList): iterable
    {
        $resolved = [];

        foreach ($classList as $className) {
            $resolved[$className] = get_class($this->container->get($className));
        }

        return $resolved;
    }
}