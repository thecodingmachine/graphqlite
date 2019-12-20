<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use phpDocumentor\Reflection\Type;
use ReflectionMethod;
use ReflectionParameter;
use function get_class;

class TypeMappingRuntimeException extends GraphQLRuntimeException
{
    /** @var Type */
    private $type;

    public static function createFromType(Type $type): self
    {
        $e       = new self("Don't know how to handle type " . (string) $type);
        $e->type = $type;

        return $e;
    }

    public static function wrapWithParamInfo(TypeMappingRuntimeException $previous, ReflectionParameter $parameter): TypeMappingRuntimeException
    {
        throw new GraphQLRuntimeException("Unexpected type in TypeMappingException. Got '" . get_class($previous->type) . '"');
    }

    public static function wrapWithReturnInfo(TypeMappingRuntimeException $previous, ReflectionMethod $method): TypeMappingRuntimeException
    {
        throw new GraphQLRuntimeException("Unexpected type in TypeMappingException. Got '" . get_class($previous->type) . '"');
    }
}
