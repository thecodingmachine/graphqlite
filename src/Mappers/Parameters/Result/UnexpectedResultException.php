<?php


namespace TheCodingMachine\GraphQLite\Mappers\Parameters\Result;


class UnexpectedResultException extends \RuntimeException
{
    public static function create(): self
    {
        return new self('Unexpected result subtype. Must be one of Success or FailInterface');
    }
}