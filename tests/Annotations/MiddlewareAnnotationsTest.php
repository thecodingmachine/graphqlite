<?php

namespace TheCodingMachine\GraphQLite\Annotations;

use PHPUnit\Framework\TestCase;

class MiddlewareAnnotationsTest extends TestCase
{

    public function testGetAnnotationByTypeException(): void
    {
        $annotations = new MiddlewareAnnotations([
            new Logged(),
            new Logged()
        ]);

        $this->expectException(TooManyAnnotationsException::class);
        $annotations->getAnnotationByType(Logged::class);
    }
}
