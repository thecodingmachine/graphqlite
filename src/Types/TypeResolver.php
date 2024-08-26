<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Types;

use GraphQL\Error\Error;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\InputType;
use GraphQL\Type\Definition\OutputType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\WrappingType;
use GraphQL\Type\Schema;
use GraphQL\Utils\AST;
use JsonException;
use RuntimeException;
use TheCodingMachine\GraphQLite\Mappers\CannotMapTypeException;
use TheCodingMachine\GraphQLite\Mappers\CannotMapTypeExceptionInterface;

/**
 * Resolves a type by its GraphQL name.
 *
 * Unlike the TypeMappers, this class can resolve standard GraphQL types (like String, ID, etc...)
 */
class TypeResolver
{
    private Schema|null $schema = null;

    public function registerSchema(Schema $schema): void
    {
        $this->schema = $schema;
    }

    /** @throws CannotMapTypeExceptionInterface|JsonException */
    public function mapNameToType(string $typeName): Type
    {
        if ($this->schema === null) {
            throw new RuntimeException('You must register a schema first before resolving types.');
        }

        try {
            $parsedOutputType = Parser::parseType($typeName);

            $type             = AST::typeFromAST([$this->schema, 'getType'], $parsedOutputType);
        } catch (Error $e) {
            throw CannotMapTypeException::createForParseError($e);
        }

        if ($type === null) {
            throw CannotMapTypeException::createForName($typeName);
        }

        return $type;
    }

    public function mapNameToOutputType(string $typeName): OutputType&Type
    {
        $type = $this->mapNameToType($typeName);
        if (! $type instanceof OutputType || ($type instanceof WrappingType && ! $type->getWrappedType() instanceof OutputType)) {
            throw CannotMapTypeException::mustBeOutputType($typeName);
        }

        return $type;
    }

    public function mapNameToInputType(string $typeName): InputType&Type
    {
        $type = $this->mapNameToType($typeName);
        if (! $type instanceof InputType || ($type instanceof WrappingType && ! $type->getWrappedType() instanceof InputType)) {
            throw CannotMapTypeException::mustBeInputType($typeName);
        }

        return $type;
    }
}
