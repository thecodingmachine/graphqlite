<?php

namespace TheCodingMachine\GraphQLite\Tests\Annotations;

use PHPUnit\Framework\TestCase;
use TheCodingMachine\GraphQLite\Annotations\Autowire;
use TheCodingMachine\GraphQLite\Annotations\ParameterAnnotations;
use TheCodingMachine\GraphQLite\Annotations\TooManyAnnotationsException;

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
