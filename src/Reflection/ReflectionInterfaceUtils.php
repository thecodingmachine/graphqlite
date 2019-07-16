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
