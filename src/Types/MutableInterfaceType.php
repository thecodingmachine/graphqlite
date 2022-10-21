<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Types;

use GraphQL\Type\Definition\InterfaceType;

/**
 * An object type built from the Type annotation
 */
class MutableInterfaceType extends InterfaceType implements MutableInterface
{
    use MutableTrait;

    /**
     * @param mixed[] $config
     * @param class-string<object>|null $className
     */
    public function __construct(array $config, string|null $className = null)
    {
        $this->status = self::STATUS_PENDING;

        parent::__construct($config);
        $this->className = $className;
    }
}
