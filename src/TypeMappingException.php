<?php


namespace TheCodingMachine\GraphQLite;


use Iterator;
use IteratorAggregate;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Iterable_;
use phpDocumentor\Reflection\Types\Mixed_;
use phpDocumentor\Reflection\Types\Object_;
use ReflectionClass;
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
        if ($previous->type instanceof Array_ || $previous->type instanceof Iterable_) {
            $typeStr = $previous->type instanceof Array_ ? 'array' : 'iterable';
            $message = sprintf('Parameter $%s in %s::%s is type-hinted to %s. Please provide an additional @param in the PHPDoc block to further specify the type of the %s. For instance: @param string[] $%s.',
                $parameter->getName(),
                $parameter->getDeclaringClass()->getName(),
                $parameter->getDeclaringFunction()->getName(),
                $typeStr,
                $typeStr,
                $parameter->getName());
        } elseif ($previous->type instanceof Mixed_) {
            $message = sprintf('Parameter $%s in %s::%s is missing a type-hint (or type-hinted to "mixed"). Please provide a better type-hint. For instance: "string $%s".',
                $parameter->getName(),
                $parameter->getDeclaringClass()->getName(),
                $parameter->getDeclaringFunction()->getName(),
                $parameter->getName());
        } else {
            if ($previous->type instanceof Object_) {
                $fqcn = (string) $previous->type->getFqsen();
                $refClass = new ReflectionClass($fqcn);
                // Note : $refClass->isIterable() is only accessible in PHP 7.2
                if ($refClass->implementsInterface(Iterator::class) || $refClass->implementsInterface(IteratorAggregate::class)) {
                    $message = sprintf('Parameter $%s in %s::%s is type-hinted to "%s", which is iterable. Please provide an additional @param in the PHPDoc block to further specify the type. For instance: @param %s|User[] $%s.',
                        $parameter->getName(),
                        $parameter->getDeclaringClass()->getName(),
                        $parameter->getDeclaringFunction()->getName(),
                        $fqcn,
                        $fqcn,
                        $parameter->getName());
                } else {
                    throw new GraphQLException("Unexpected type in TypeMappingException. Got a non iterable '".$fqcn.'"');
                }
            } else {
                throw new GraphQLException("Unexpected type in TypeMappingException. Got '".get_class($previous->type).'"');
            }
        }

        $e = new self($message, 0, $previous);
        $e->type = $previous->type;
        return $e;
    }

    public static function wrapWithReturnInfo(TypeMappingException $previous, ReflectionMethod $method): TypeMappingException
    {
        if ($previous->type instanceof Array_ || $previous->type instanceof Iterable_) {
            $typeStr = $previous->type instanceof Array_ ? 'array' : 'iterable';
            $message = sprintf('Return type in %s::%s is type-hinted to %s. Please provide an additional @return in the PHPDoc block to further specify the type of the array. For instance: @return string[]',
                $method->getDeclaringClass()->getName(),
                $method->getName(),
                $typeStr);
        } elseif ($previous->type instanceof Mixed_) {
            $message = sprintf('Return type in %s::%s is missing a type-hint (or type-hinted to "mixed"). Please provide a better type-hint.',
                $method->getDeclaringClass()->getName(),
                $method->getName());
        } else {
            if ($previous->type instanceof Object_) {
                $fqcn = (string) $previous->type->getFqsen();
                $refClass = new ReflectionClass($fqcn);
                // Note : $refClass->isIterable() is only accessible in PHP 7.2
                if ($refClass->implementsInterface(Iterator::class) || $refClass->implementsInterface(IteratorAggregate::class)) {
                    $message = sprintf('Return type in %s::%s is type-hinted to "%s", which is iterable. Please provide an additional @param in the PHPDoc block to further specify the type. For instance: @return %s|User[]',
                        $method->getDeclaringClass()->getName(),
                        $method->getName(),
                        $fqcn,
                        $fqcn);
                } else {
                    throw new GraphQLException("Unexpected type in TypeMappingException. Got a non iterable '".$fqcn.'"');
                }
            } else {
                throw new GraphQLException("Unexpected type in TypeMappingException. Got '".get_class($previous->type).'"');
            }
        }

        $e = new self($message, 0, $previous);
        $e->type = $previous->type;
        return $e;
    }
}
