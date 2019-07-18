<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Mappers;

use Exception;
use GraphQL\Error\ClientAware;

class PorpaginasMissingParameterException extends Exception implements ClientAware, CannotMapTypeExceptionInterface
{
    use CannotMapTypeTrait;

    public static function missingLimit(): self
    {
        return new self('In the items field of a result set, you cannot add a "offset" without also adding a "limit"');
    }

    public static function noSubType(): self
    {
        return new self('Result sets implementing Porpaginas need to have a subtype. Please define it using @return annotation. For instance: "@return User[]"');
    }

    /**
     * Returns true when exception message is safe to be displayed to a client.
     */
    public function isClientSafe(): bool
    {
        return true;
    }

    /**
     * Returns string describing a category of the error.
     *
     * Value "graphql" is reserved for errors produced by query parsing or validation, do not use it.
     */
    public function getCategory(): string
    {
        return 'pagination';
    }
}
