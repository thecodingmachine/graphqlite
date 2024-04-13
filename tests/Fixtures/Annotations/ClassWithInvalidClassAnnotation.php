<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Annotations;

/**
 * No namespace here
 */
#[foo]
class ClassWithInvalidClassAnnotation
{
    #[foo]
    public function testMethod(): void
    {
    }
}
