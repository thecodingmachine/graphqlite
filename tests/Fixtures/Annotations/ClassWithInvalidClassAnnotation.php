<?php


namespace TheCodingMachine\GraphQLite\Fixtures\Annotations;

/**
 * No namespace here
 *
 * @foo()
 */
class ClassWithInvalidClassAnnotation
{
    /**
     * @foo
     */
    public function testMethod(): void
    {

    }
}
