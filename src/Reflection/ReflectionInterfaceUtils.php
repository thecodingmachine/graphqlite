<?php


namespace TheCodingMachine\GraphQLite\Reflection;


use function array_diff_key;
use ReflectionClass;

class ReflectionInterfaceUtils
{

    /**
     * Returns a list of all interfaces directly implemented by this class/interface.
     * "Super" interfaces are not returned.
     *
     * @param ReflectionClass $reflectionClass
     * @return array<string, ReflectionClass> Interfaces indexed by FQCN
     */
    public static function getDirectlyImplementedInterfaces(ReflectionClass $reflectionClass): array
    {
        $interfaces = $reflectionClass->getInterfaces();

        $subInterfaces = [];
        foreach ($interfaces as $interface) {
            $subInterfaces += $interface->getInterfaces();
        }

        return array_diff_key($interfaces, $subInterfaces);
    }
}