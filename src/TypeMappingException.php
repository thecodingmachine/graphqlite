<?php


namespace TheCodingMachine\GraphQL\Controllers;


use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Mixed_;
use \ReflectionMethod;

class TypeMappingException extends GraphQLException
{
    private $type;

    public static function createFromType(Type $type): self
    {
        $e = new self("Don't know how to handle type ".(string) $type);
        $e->type = $type;
        return $e;
    }

    public static function wrapWithParamInfo(TypeMappingException $previous, \ReflectionParameter $parameter): TypeMappingException
    {
        if ($previous->type instanceof Array_) {
            $message = sprintf('Parameter $%s in %s::%s is type-hinted to array. Please provide an additional @param in the PHPDoc block to further specify the type of the array. For instance: @param string[] $%s.',
                $parameter->getName(),
                $parameter->getDeclaringClass()->getName(),
                $parameter->getDeclaringFunction()->getName(),
                $parameter->getName());
        } elseif ($previous->type instanceof Mixed_) {
            $message = sprintf('Parameter $%s in %s::%s is missing a type-hint (or type-hinted to "mixed"). Please provide a better type-hint. For instance: "string $%s".',
                $parameter->getName(),
                $parameter->getDeclaringClass()->getName(),
                $parameter->getDeclaringFunction()->getName(),
                $parameter->getName());
        } else {
            throw new GraphQLException("Unexpected type in TypeMappingException");
        }

        $e = new self($message, 0, $previous);
        $e->type = $previous->type;
        return $e;
    }

    public static function wrapWithReturnInfo(TypeMappingException $previous, ReflectionMethod $method): TypeMappingException
    {
        if ($previous->type instanceof Array_) {
            $message = sprintf('Return type in %s::%s is type-hinted to array. Please provide an additional @return in the PHPDoc block to further specify the type of the array. For instance: @return string[]',
                $method->getDeclaringClass()->getName(),
                $method->getName());
        } elseif ($previous->type instanceof Mixed_) {
            $message = sprintf('Return type in %s::%s is missing a type-hint (or type-hinted to "mixed"). Please provide a better type-hint.',
                $method->getDeclaringClass()->getName(),
                $method->getName());
        } else {
            throw new GraphQLException("Unexpected type in TypeMappingException");
        }

        $e = new self($message, 0, $previous);
        $e->type = $previous->type;
        return $e;
    }
}
