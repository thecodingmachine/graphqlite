<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Object_;
use ReflectionMethod;
use ReflectionParameter;
use Webmozart\Assert\Assert;
use function get_class;
use function sprintf;

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
        $declaringClass = $parameter->getDeclaringClass();
        Assert::notNull($declaringClass, 'Parameter passed must be a parameter of a method, not a parameter of a function.');

        if ($previous->type instanceof Compound) {
            $message = sprintf(
                'Parameter $%s in %s::%s is type-hinted to "' . $previous->type . '". Type-hinting a parameter to a union type is forbidden in GraphQL. Only return types can be union types.',
                $parameter->getName(),
                $declaringClass->getName(),
                $parameter->getDeclaringFunction()->getName()
            );
        } elseif (! ($previous->type instanceof Object_)) {
            throw new GraphQLRuntimeException("Unexpected type in TypeMappingException. Got '" . get_class($previous->type) . '"');
        } else {
            $fqcn     = (string) $previous->type->getFqsen();

            if ($fqcn !== '\\DateTime') {
                throw new GraphQLRuntimeException("Unexpected type in TypeMappingException. Got a '" . $fqcn . '"');
            }

            $message = sprintf(
                'Parameter $%s in %s::%s is type-hinted to "DateTime". Type-hinting a parameter against DateTime is not allowed. Please use the DateTimeImmutable type instead.',
                $parameter->getName(),
                $declaringClass->getName(),
                $parameter->getDeclaringFunction()->getName()
            );
        }

        $e       = new self($message, 0, $previous);
        $e->type = $previous->type;

        return $e;
    }

    public static function wrapWithReturnInfo(TypeMappingRuntimeException $previous, ReflectionMethod $method): TypeMappingRuntimeException
    {
        if (! ($previous->type instanceof Object_)) {
            throw new GraphQLRuntimeException("Unexpected type in TypeMappingException. Got '" . get_class($previous->type) . '"');
        }

        $fqcn     = (string) $previous->type->getFqsen();
        if ($fqcn !== '\\DateTime') {
            throw new GraphQLRuntimeException("Unexpected type in TypeMappingException. Got a '" . $fqcn . '"');
        }

        $message = sprintf(
            'Return type in %s::%s is type-hinted to "DateTime". Type-hinting a parameter against DateTime is not allowed. Please use the DateTimeImmutable type instead.',
            $method->getDeclaringClass()->getName(),
            $method->getName()
        );

        $e       = new self($message, 0, $previous);
        $e->type = $previous->type;

        return $e;
    }
}
