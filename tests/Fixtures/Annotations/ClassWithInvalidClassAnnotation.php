<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Tests\Fixtures\Annotations;

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
