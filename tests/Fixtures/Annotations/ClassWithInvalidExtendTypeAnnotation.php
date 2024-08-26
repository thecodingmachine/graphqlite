<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Annotations;

use TheCodingMachine\GraphQLite\Annotations\ExtendType;

#[ExtendType(class: 'foo')]
class ClassWithInvalidExtendTypeAnnotation
{
}
