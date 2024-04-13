<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Annotations;

/**
 * No namespace here
 */
#[Type]
class ClassWithInvalidTypeAnnotation
{
    #[Field]
    public function testMethod(): void
    {
    }
}
