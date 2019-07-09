<?php

namespace TheCodingMachine\GraphQLite\Annotations;

use PHPUnit\Framework\TestCase;

class ParameterAnnotationsTest extends TestCase
{

    public function testGetAnnotationByTypeException(): void
    {
        $annotations = new ParameterAnnotations([
            new Autowire(['for'=>'foo']),
            new Autowire(['for'=>'foo'])
        ]);

        $this->expectException(TooManyAnnotationsException::class);
        $annotations->getAnnotationByType(Autowire::class);
    }
}
