<?php


namespace TheCodingMachine\GraphQL\Controllers;


class FieldNotFoundException extends \RuntimeException
{
    public static function missingField(string $className, string $fieldName): self
    {
        throw new self(sprintf('Could not find a getter or a isser for field "%s". Looked for: "%s::get%s()", "%s::is%s()"',
            $fieldName, $className, \ucfirst($fieldName), $className, \ucfirst($fieldName)));
    }

    public static function wrapWithCallerInfo(self $e, string $className): self
    {
        throw new self(sprintf('There is an issue with a @ExposedField annotation in class "%s": %s',
            $className, $e->getMessage()), 0, $e);
    }
}
