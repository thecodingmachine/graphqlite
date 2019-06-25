<?php

namespace TheCodingMachine\GraphQLite\Annotations;

use BadMethodCallException;
use PHPUnit\Framework\TestCase;

class ParameterTest extends TestCase
{

    public function testException()
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('The @Parameter annotation must be passed a target. For instance: "@Parameter(for="$input", inputType="MyInputType")"');
        new Parameter(['annotations'=>[]]);
    }

    public function testGetAnnotationByTypeException()
    {
        $parameter = new Parameter([
            'for' => 'foo',
            'annotations' => [
                new Autowire([]),
                new Autowire([])
            ]
        ]);

        $this->expectException(TooManyAnnotationsException::class);
        $parameter->getAnnotationByType(Autowire::class);
    }

    public function testGetAllAnnotations()
    {
        $annotations = [
            new Autowire([]),
            new Autowire([])
        ];

        $parameter = new Parameter([
            'for' => 'foo',
            'annotations' => $annotations
        ]);

        $this->assertSame($annotations, $parameter->getAllAnnotations());
    }
}
