<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Types;

use Exception;
use GraphQL\Error\InvariantViolation;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\ObjectType;
use RuntimeException;

/**
 * An object type built from the Type annotation
 */
class MutableInterfaceType extends InterfaceType implements MutableInterface
{
    use MutableTrait;

    /**
     * @param mixed[] $config
     */
    public function __construct(array $config, ?string $className = null)
    {
        $this->status = self::STATUS_PENDING;

        parent::__construct($config);
        $this->className = $className;
    }
}
