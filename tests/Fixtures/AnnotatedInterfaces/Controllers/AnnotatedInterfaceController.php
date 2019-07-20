<?php


namespace TheCodingMachine\GraphQLite\Fixtures\AnnotatedInterfaces\Controllers;


use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\GraphQLite\Fixtures\AnnotatedInterfaces\Types\BazInterface;
use TheCodingMachine\GraphQLite\Fixtures\AnnotatedInterfaces\Types\ClassA;
use TheCodingMachine\GraphQLite\Fixtures\AnnotatedInterfaces\Types\ClassD;
use TheCodingMachine\GraphQLite\Fixtures\AnnotatedInterfaces\Types\FooInterface;

class AnnotatedInterfaceController
{
    /**
     * @Query()
     */
    public function getClassA(): ClassA
    {
        return new ClassA();
    }

    /**
     * @Query()
     */
    public function getFoo(): FooInterface
    {
        return new ClassD();
    }

    /**
     * @Query() Unsupported: annotating an interface that has no @Type annotation.
     */
    /*public function getBaz(): BazInterface
    {
        return new ClassD();
    }*/

    /**
     * @Query()
     */
    public function getClassD(): ClassD
    {
        return new ClassD();
    }
}
