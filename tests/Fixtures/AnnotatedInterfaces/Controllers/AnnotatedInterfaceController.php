<?php


namespace TheCodingMachine\GraphQLite\Fixtures\AnnotatedInterfaces\Controllers;


use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\GraphQLite\Fixtures\AnnotatedInterfaces\Types\BazInterface;
use TheCodingMachine\GraphQLite\Fixtures\AnnotatedInterfaces\Types\ClassA;
use TheCodingMachine\GraphQLite\Fixtures\AnnotatedInterfaces\Types\ClassD;
use TheCodingMachine\GraphQLite\Fixtures\AnnotatedInterfaces\Types\FooInterface;
use TheCodingMachine\GraphQLite\Fixtures\AnnotatedInterfaces\Types\NotAnnotatedQux;
use TheCodingMachine\GraphQLite\Fixtures\AnnotatedInterfaces\Types\QuxInterface;
use TheCodingMachine\GraphQLite\Fixtures\AnnotatedInterfaces\Types\WizzInterface;

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
    public function getClassDAsWizInterface(): WizzInterface
    {
        return new ClassD();
    }

    /**
     * @Query()
     */
    public function getQux(): QuxInterface
    {
        return new NotAnnotatedQux();
    }
}
