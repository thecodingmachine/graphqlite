<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Types;

use DateTime;
use DateTimeImmutable;
use Exception;
use GraphQL\Error\InvariantViolation;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Utils\Utils;
use TheCodingMachine\GraphQLite\GraphQLRuntimeException;

class DateTimeType extends ScalarType
{
    /** @var string */
    public $name = 'DateTime';

    /** @var string */
    public $description = 'The `DateTime` scalar type represents time data, represented as an ISO-8601 encoded UTC date string.';

    /**
     * @param mixed $value
     */
    public function serialize($value): string
    {
        if (! $value instanceof DateTimeImmutable) {
            throw new InvariantViolation('DateTime is not an instance of DateTimeImmutable: ' . Utils::printSafe($value));
        }

        return $value->format(DateTime::ATOM);
    }

    /**
     * @param mixed $value
     */
    public function parseValue($value): ?DateTimeImmutable
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof DateTimeImmutable) {
            return $value;
        }

        return new DateTimeImmutable($value);
    }

    /**
     * Parses an externally provided literal value (hardcoded in GraphQL query) to use as an input
     *
     * In the case of an invalid node or value this method must throw an Exception
     *
     * @param array<string, mixed>|null $variables
     *
     * @return mixed
     *
     * @throws Exception
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint
     */
    public function parseLiteral($valueNode, ?array $variables = null)
    {
        if ($valueNode instanceof StringValueNode) {
            return $valueNode->value;
        }

        // Intentionally without message, as all information already in wrapped Exception
        throw new GraphQLRuntimeException();
    }
}
