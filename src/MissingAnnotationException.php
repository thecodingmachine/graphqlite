<?php


namespace TheCodingMachine\GraphQL\Controllers;


class MissingAnnotationException extends \RuntimeException
{
    public static function missingTypeException(): self
    {
        return new self('You cannot use the @SourceField annotation without also adding a @Type annotation.');
    }
}