<?php

namespace TheCodingMachine\GraphQLite\Tests\Annotations;

use PHPUnit\Framework\TestCase;
use TheCodingMachine\GraphQLite\Annotations\Logged;
use TheCodingMachine\GraphQLite\Annotations\MiddlewareAnnotations;
use TheCodingMachine\GraphQLite\Annotations\TooManyAnnotationsException;

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
