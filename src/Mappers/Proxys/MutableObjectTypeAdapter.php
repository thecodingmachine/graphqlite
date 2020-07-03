<?php


namespace TheCodingMachine\GraphQLite\Mappers\Proxys;

use Exception;
use GraphQL\Error\InvariantViolation;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Utils\Utils;
use RuntimeException;
use TheCodingMachine\GraphQLite\Types\MutableInterface;
use TheCodingMachine\GraphQLite\Types\NoFieldsException;
use function assert;
use function call_user_func;
use function is_array;
use function is_callable;
use function is_string;
use function sprintf;

/**
 * An adapter class (actually a proxy) that adds the "mutable" feature to any Webonyx ObjectType.
 *
 * @internal
 */
class MutableObjectTypeAdapter extends ObjectType implements MutableInterface
{
    /** @use MutableAdapterTrait<ObjectType> */
    use MutableAdapterTrait;

    public function __construct(ObjectType $type, ?string $className = null)
    {
        $this->type = $type;
        $this->className = $className;
        $this->name = $type->name;
        $this->config = $type->config;
        $this->astNode = $type->astNode;
        $this->extensionASTNodes = $type->extensionASTNodes;
        $this->resolveFieldFn = $type->resolveFieldFn;
    }

    /**
     * @return InterfaceType[]
     */
    public function getInterfaces()
    {
        $type = $this->type;
        assert($type instanceof ObjectType);
        return $type->getInterfaces();
    }

    /**
     * @param mixed[]      $value
     * @param mixed[]|null $context
     *
     * @return bool|null
     */
    public function isTypeOf($value, $context, ResolveInfo $info)
    {
        $type = $this->type;
        assert($type instanceof ObjectType);
        return $type->isTypeOf($value, $context, $info);
    }

    /**
     * @param InterfaceType $iface
     *
     * @return bool
     */
    public function implementsInterface($iface)
    {
        $type = $this->type;
        assert($type instanceof ObjectType);
        return $type->implementsInterface($iface);
    }
}
