<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Types;

use GraphQL\Language\AST\Node;
use GraphQL\Type\Definition\ScalarType;
use TheCodingMachine\GraphQLite\GraphQLRuntimeException;

class VoidType extends ScalarType
{
    public string $name = 'Void';

    public string|null $description = 'The `Void` scalar type represents no value being returned.';

    public function serialize(mixed $value): bool|null
    {
        // Return type contains `bool` because `null` is only allowed as a standalone type since PHP 8.2.
        return null;
    }

    public function parseValue(mixed $value): never
    {
        throw new GraphQLRuntimeException();
    }

    public function parseLiteral(Node $valueNode, array|null $variables = null): never
    {
        throw new GraphQLRuntimeException();
    }
}
