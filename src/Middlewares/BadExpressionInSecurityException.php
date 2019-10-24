<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Middlewares;

use Exception;
use TheCodingMachine\GraphQLite\QueryFieldDescriptor;
use Throwable;

/**
 * Exception wrapping exceptions occurring when the Security annotation is evaluated
 */
class BadExpressionInSecurityException extends Exception
{
    public static function wrapException(Throwable $e, QueryFieldDescriptor $fieldDescriptor): self
    {
        $refMethod = $fieldDescriptor->getRefMethod();
        $message = 'An error occurred while evaluating expression in @Security annotation of method "' . $refMethod->getDeclaringClass()->getName() . '::' . $refMethod->getName() . '": ' . $e->getMessage();

        return new self($message, $e->getCode(), $e);
    }
}
