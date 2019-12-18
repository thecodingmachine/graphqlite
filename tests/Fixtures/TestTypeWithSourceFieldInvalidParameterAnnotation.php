<?php


namespace TheCodingMachine\GraphQLite\Fixtures;

use TheCodingMachine\GraphQLite\Annotations\SourceField;
use TheCodingMachine\GraphQLite\Annotations\Type;
use TheCodingMachine\GraphQLite\Annotations\HideParameter;

/**
 * @Type(class=TestObject::class)
 * @SourceField(name="sibling", annotations={@HideParameter(for="id")})
 */
class TestTypeWithSourceFieldInvalidParameterAnnotation
{
}
