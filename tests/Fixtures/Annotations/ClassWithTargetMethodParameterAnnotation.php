<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Annotations;

use stdClass;

/** @internal */
final class ClassWithTargetMethodParameterAnnotation
{
    #[TargetMethodParameterAnnotation(target: 'bar')]
    public function method(stdClass $bar): void
    {
    }

    #[TargetMethodParameterAnnotation(target: 'unexistent')]
    public function methodWithInvalidAnnotation(stdClass $bar): void
    {
    }
}
