<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Middlewares;

use Exception;
use TheCodingMachine\GraphQLite\InputFieldDescriptor;
use TheCodingMachine\GraphQLite\QueryFieldDescriptor;
use Throwable;

/**
 * Exception wrapping exceptions occurring when the Security annotation is evaluated
 */
class BadExpressionInSecurityException extends Exception
{
    public static function wrapException(Throwable $e, QueryFieldDescriptor|InputFieldDescriptor $fieldDescriptor): self
    {
        $originalResolver = $fieldDescriptor->getOriginalResolver();
        $message = 'An error occurred while evaluating expression in @Security annotation of method "' . $originalResolver->toString() . '": ' . $e->getMessage();

        return new self($message, $e->getCode(), $e);
    }
}
