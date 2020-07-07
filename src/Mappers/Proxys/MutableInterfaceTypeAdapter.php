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
use TheCodingMachine\GraphQLite\Types\MutableInterfaceType;
use TheCodingMachine\GraphQLite\Types\NoFieldsException;
use function call_user_func;
use function is_array;
use function is_callable;
use function is_string;
use function sprintf;

/**
 * An adapter class (actually a proxy) that adds the "mutable" feature to any Webonyx InterfaceType.
 *
 * @internal
 */
class MutableInterfaceTypeAdapter extends MutableInterfaceType
{
    /** @use MutableAdapterTrait<InterfaceType> */
    use MutableAdapterTrait;

    public function __construct(InterfaceType $type, ?string $className = null)
    {
        $this->type = $type;
        $this->className = $className;
        $this->name = $type->name;
        $this->config = $type->config;
        $this->astNode = $type->astNode;
        $this->extensionASTNodes = $type->extensionASTNodes;
    }
}
