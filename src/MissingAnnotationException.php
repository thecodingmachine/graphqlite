<?php


namespace TheCodingMachine\GraphQLite;


class MissingAnnotationException extends \RuntimeException
{
    public static function missingTypeExceptionToUseSourceField(): self
    {
        return new self('You cannot use the @SourceField annotation without also adding a @Type annotation or a @ExtendType annotation.');
    }

    public static function missingTypeException(): self
    {
        return new self('GraphQL type classes must provide a @Type annotation.');
    }

    public static function missingExtendTypeException(): self
    {
        return new self('Expected a @ExtendType annotation.');
    }
}
