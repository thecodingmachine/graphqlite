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

    /**
     * Raised while the schema is built, so a malformed expression is a startup error naming the
     * field rather than a surprise inside a resolver on some later request.
     */
    public static function fromSyntaxError(Throwable $e, QueryFieldDescriptor|InputFieldDescriptor $fieldDescriptor, string $expression): self
    {
        $originalResolver = $fieldDescriptor->getOriginalResolver();
        $message = 'The expression in the #[Security] attribute of "' . $originalResolver->toString() . '" is not valid: '
            . $e->getMessage() . ' Expression: "' . $expression . '".';

        return new self($message, $e->getCode(), $e);
    }
}
