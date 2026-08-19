<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Middlewares;

use Exception;
use TheCodingMachine\GraphQLite\InputFieldDescriptor;
use TheCodingMachine\GraphQLite\QueryFieldDescriptor;

use function get_debug_type;
use function implode;
use function is_array;

/**
 * Thrown when a #[Security] check resolves to something other than a bool.
 *
 * An authorization decision that depends on PHP's truthiness table is a bug waiting to be
 * exploited: under a loose check the string 'false' permits, while 0, '' and null deny.
 */
class NonBooleanSecurityResultException extends Exception
{
    /** @param array{class-string, string}|object|null $rule */
    public static function fromRule(
        array|object|null $rule,
        QueryFieldDescriptor|InputFieldDescriptor $fieldDescriptor,
        mixed $returned,
    ): self
    {
        return new self(
            'The #[Security] rule ' . self::describe($rule) . ' guarding "'
            . $fieldDescriptor->getOriginalResolver()->toString() . '" must return a bool, but returned '
            . get_debug_type($returned) . '.',
        );
    }

    public static function fromExpression(
        string $expression,
        QueryFieldDescriptor|InputFieldDescriptor $fieldDescriptor,
        mixed $returned,
    ): self
    {
        return new self(
            'The #[Security] expression guarding "' . $fieldDescriptor->getOriginalResolver()->toString()
            . '" must evaluate to a bool, but evaluated to ' . get_debug_type($returned)
            . '. Compare explicitly, for instance "user !== null" rather than "user". Expression: "'
            . $expression . '".',
        );
    }

    /** @param array{class-string, string}|object|null $rule */
    private static function describe(array|object|null $rule): string
    {
        if (is_array($rule)) {
            return implode('::', $rule) . '()';
        }

        return $rule === null ? '(unknown)' : $rule::class;
    }
}
