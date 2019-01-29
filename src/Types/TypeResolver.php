<?php


namespace TheCodingMachine\GraphQLite\Types;

use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
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
    /**
     * @var Schema
     */
    private $schema;

    public function registerSchema(Schema $schema)
    {
        $this->schema = $schema;
    }

    /**
     * @param string $typeName
     * @return Type
     * @throws CannotMapTypeExceptionInterface
     */
    public function mapNameToType(string $typeName): Type
    {
        if ($this->schema === null) {
            throw new RuntimeException('You must register a schema first before resolving types.');
        }
        
        $type = $this->schema->getType($typeName);
        if ($type === null) {
            throw CannotMapTypeException::createForName($typeName);
        }

        return $type;
    }
}
