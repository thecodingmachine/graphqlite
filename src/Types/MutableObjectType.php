<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Types;

use GraphQL\Type\Definition\ObjectType;

/**
 * An object type built from the Type annotation.
 *
 * @phpstan-import-type ObjectConfig from ObjectType
 */
class MutableObjectType extends ObjectType implements MutableInterface
{
    use MutableTrait;

    /**
     * @param ObjectConfig              $config
     * @param class-string<object>|null $className
     */
    public function __construct(array $config, string|null $className = null)
    {
        $this->status = self::STATUS_PENDING;

        parent::__construct($config);
        $this->className = $className;
    }
}
