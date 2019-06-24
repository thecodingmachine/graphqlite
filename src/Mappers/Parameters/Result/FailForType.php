<?php


namespace TheCodingMachine\GraphQLite\Mappers\Parameters\Result;


use function get_class;
use Iterator;
use IteratorAggregate;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Iterable_;
use phpDocumentor\Reflection\Types\Mixed_;
use phpDocumentor\Reflection\Types\Object_;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use function sprintf;
use TheCodingMachine\GraphQLite\GraphQLException;
use TheCodingMachine\GraphQLite\TypeMappingException;
use Webmozart\Assert\Assert;

class FailForType implements Fail
{
    /**
     * @var string
     */
    private $message;

    /** @var Type */
    private $type;

    public function __construct(string $message)
    {
        $this->message = $message;
    }

    public static function createFromType(Type $type): self
    {
        $e       = new self("Don't know how to handle type " . (string) $type);
        $e->type = $type;

        return $e;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return Type
     */
    public function getType(): Type
    {
        return $this->type;
    }

    public function addParamInfo(self $err, ReflectionParameter $parameter): void
    {
        $declaringClass = $parameter->getDeclaringClass();
        Assert::notNull($declaringClass, 'Parameter passed must be a parameter of a method, not a parameter of a function.');
        if ($err->type instanceof Array_ || $err->type instanceof Iterable_) {
            $typeStr = $err->type instanceof Array_ ? 'array' : 'iterable';
            $this->message = sprintf(
                'Parameter $%s in %s::%s is type-hinted to %s. Please provide an additional @param in the PHPDoc block to further specify the type of the %s. For instance: @param string[] $%s.',
                $parameter->getName(),
                $declaringClass->getName(),
                $parameter->getDeclaringFunction()->getName(),
                $typeStr,
                $typeStr,
                $parameter->getName()
            );
        } elseif ($err->type instanceof Mixed_) {
            $this->message = sprintf(
                'Parameter $%s in %s::%s is missing a type-hint (or type-hinted to "mixed"). Please provide a better type-hint. For instance: "string $%s".',
                $parameter->getName(),
                $declaringClass->getName(),
                $parameter->getDeclaringFunction()->getName(),
                $parameter->getName()
            );
        } else {
            if (! ($err->type instanceof Object_)) {
                throw new GraphQLException("Unexpected type in error. Got '" . get_class($err->type) . '"');
            }

            $fqcn     = (string) $err->type->getFqsen();
            $refClass = new ReflectionClass($fqcn);
            if (! $refClass->isIterable()) {
                throw new GraphQLException("Unexpected type in TypeMappingException. Got a non iterable '" . $fqcn . '"');
            }

            $this->message = sprintf(
                'Parameter $%s in %s::%s is type-hinted to "%s", which is iterable. Please provide an additional @param in the PHPDoc block to further specify the type. For instance: @param %s|User[] $%s.',
                $parameter->getName(),
                $declaringClass->getName(),
                $parameter->getDeclaringFunction()->getName(),
                $fqcn,
                $fqcn,
                $parameter->getName()
            );
        }
    }

    public function addReturnInfo(self $err, ReflectionMethod $method): void
    {
        if ($err->type instanceof Array_ || $err->type instanceof Iterable_) {
            $typeStr = $err->type instanceof Array_ ? 'array' : 'iterable';
            $this->message = sprintf(
                'Return type in %s::%s is type-hinted to %s. Please provide an additional @return in the PHPDoc block to further specify the type of the array. For instance: @return string[]',
                $method->getDeclaringClass()->getName(),
                $method->getName(),
                $typeStr
            );
        } elseif ($err->type instanceof Mixed_) {
            $this->message = sprintf(
                'Return type in %s::%s is missing a type-hint (or type-hinted to "mixed"). Please provide a better type-hint.',
                $method->getDeclaringClass()->getName(),
                $method->getName()
            );
        } else {
            if (! ($err->type instanceof Object_)) {
                throw new GraphQLException("Unexpected type in TypeMappingException. Got '" . get_class($err->type) . '"');
            }

            $fqcn     = (string) $err->type->getFqsen();
            $refClass = new ReflectionClass($fqcn);
            // Note : $refClass->isIterable() is only accessible in PHP 7.2
            if (! $refClass->implementsInterface(Iterator::class) && ! $refClass->implementsInterface(IteratorAggregate::class)) {
                throw new GraphQLException("Unexpected type in TypeMappingException. Got a non iterable '" . $fqcn . '"');
            }

            $this->message = sprintf(
                'Return type in %s::%s is type-hinted to "%s", which is iterable. Please provide an additional @param in the PHPDoc block to further specify the type. For instance: @return %s|User[]',
                $method->getDeclaringClass()->getName(),
                $method->getName(),
                $fqcn,
                $fqcn
            );
        }
    }

    /**
     * If the result is an error, an exception is thrown
     */
    public function throwIfError(): void
    {
        throw TypeMappingException::createFromFail($this, $this->type);
    }
}