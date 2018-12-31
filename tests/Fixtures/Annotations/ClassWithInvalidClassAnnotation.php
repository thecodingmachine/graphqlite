<?php


namespace TheCodingMachine\GraphQL\Controllers\Fixtures\Annotations;

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
    public function testMethod()
    {

    }
}
