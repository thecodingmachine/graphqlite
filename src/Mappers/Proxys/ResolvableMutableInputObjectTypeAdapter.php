<?php


namespace TheCodingMachine\GraphQLite\Mappers\Proxys;

use GraphQL\Type\Definition\InputType;
use TheCodingMachine\GraphQLite\FieldsBuilder;
use TheCodingMachine\GraphQLite\Types\ResolvableMutableInputObjectType;

/**
 * An adapter class (actually a proxy) that adds the "mutable" + "resolvable" feature to any Webonyx InputType.
 *
 * @internal
 */
class ResolvableMutableInputObjectTypeAdapter extends ResolvableMutableInputObjectType
{
    public function __construct(InputType $inputType)
    {

    }
}
