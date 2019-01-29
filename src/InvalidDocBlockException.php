<?php


namespace TheCodingMachine\GraphQLite;


class InvalidDocBlockException extends GraphQLException
{
    public static function tooManyReturnTags(\ReflectionMethod $refMethod): self
    {
        throw new self('Method '.$refMethod->getDeclaringClass()->getName().'::'.$refMethod->getName().' has several @return annotations.');
    }
}