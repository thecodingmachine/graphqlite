<?php


namespace TheCodingMachine\GraphQLite\Fixtures\AnnotatedInterfaces\Types;

use TheCodingMachine\GraphQLite\Annotations\Type;

/**
 * @Type()
 */
class ClassA implements FooInterface, BarInterface, BazInterface
{

    public function getBar(): string
    {
        return 'bar';
    }

    public function getFoo(): string
    {
        return 'foo';
    }

    public function getParentValue(): string
    {
        return 'parent';
    }

    public function grandFather(): string
    {
        return 'grandFather';
    }

    public function grandMother(): string
    {
        return 'grandMother';
    }
}