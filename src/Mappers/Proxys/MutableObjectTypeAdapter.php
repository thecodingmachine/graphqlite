<?php


namespace TheCodingMachine\GraphQLite\Mappers\Proxys;

use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use TheCodingMachine\GraphQLite\Mappers\Proxys\MutableAdapterTrait;
use TheCodingMachine\GraphQLite\Types\MutableObjectType;
use function assert;

/**
 * An adapter class (actually a proxy) that adds the "mutable" feature to any Webonyx ObjectType.
 *
 * @internal
 */
final class MutableObjectTypeAdapter extends MutableObjectType
{
    use MutableAdapterTrait;

    public function __construct(ObjectType $type, ?string $className = null)
    {
        $this->type = $type;
        $this->className = $className;
        $this->name = $type->name;
        $this->description = $type->description;
        $this->config = $type->config;
        $this->astNode = $type->astNode;
        $this->extensionASTNodes = $type->extensionASTNodes;
        $this->resolveFieldFn = $type->resolveFieldFn;
    }

    /**
     * @return InterfaceType[]
     */
    public function getInterfaces(): array
    {
        $type = $this->type;
        assert($type instanceof ObjectType);
        return $type->getInterfaces();
    }

    /**
     * @param mixed[]      $value
     * @param mixed[]|null $context
     *
     * @return bool|\GraphQL\Deferred|null
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
    public function implementsInterface($iface): bool
    {
        $type = $this->type;
        assert($type instanceof ObjectType);
        return $type->implementsInterface($iface);
    }
}
