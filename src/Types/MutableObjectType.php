<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Types;

use GraphQL\Type\Definition\ObjectType;

/**
 * An object type built from the Type annotation
 */
class MutableObjectType extends ObjectType implements MutableInterface
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
