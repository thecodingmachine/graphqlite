<?php


namespace TheCodingMachine\GraphQL\Controllers\Hydrators;


use Exception;

class CannotHydrateException extends Exception
{
    public static function createForInputType(string $inputTypeName): self
    {
        return new self('Cannot hydrate type "'.$inputTypeName.'"');
    }
}
