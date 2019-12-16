<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Reflection;

use ReflectionClass;
use function array_diff_key;

class ReflectionInterfaceUtils
{
    /**
     * Returns a list of all interfaces directly implemented by this class/interface.
     * "Super" interfaces are not returned.
     *
     * @param ReflectionClass<T> $reflectionClass
     *
     * @return array<string,ReflectionClass<T>> Interfaces indexed by FQCN
     *
     * @template T of object
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
