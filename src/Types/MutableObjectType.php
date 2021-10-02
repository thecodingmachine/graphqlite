<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Types;

use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\ObjectType;

/**
 * An object type built from the Type annotation
 */
class MutableObjectType extends ObjectType implements MutableInterface
{
    use MutableTrait;

    /**
     * @param mixed[] $config
     * @param class-string<object>|null $className
     */
    public function __construct(array $config, ?string $className = null)
    {
        $this->status = self::STATUS_PENDING;

        parent::__construct($config);
        $this->className = $className;
    }

    public function findField(string $name): ?FieldDefinition
    {
        $field = parent::findField($name);
        if ($field) {
            return $field;
        }

        $fields = $this->getFields();
        if (! isset($fields[$name])) {
            return null;
        }

        return $fields[$name];
    }
}
