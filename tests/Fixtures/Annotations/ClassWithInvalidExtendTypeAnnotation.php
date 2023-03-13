<?php


namespace TheCodingMachine\GraphQLite\Fixtures\Annotations;

use TheCodingMachine\GraphQLite\Annotations\ExtendType;


#[ExtendType(class: 'foo')]
class ClassWithInvalidExtendTypeAnnotation
{
}
