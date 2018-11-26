<?php


namespace TheCodingMachine\GraphQL\Controllers\Fixtures\Interfaces\Types;

use TheCodingMachine\GraphQL\Controllers\Annotations\SourceField;
use TheCodingMachine\GraphQL\Controllers\Annotations\Type;
use TheCodingMachine\GraphQL\Controllers\Fixtures\Interfaces\ClassA;

/**
 * @Type(class=ClassA::class)
 * @SourceField(name="foo")
 */
class ClassAType
{
    // TODO: question: DO WE INHERIT AUTOMATICALLY OR NOT?

    // Do we declare interfaces or not?
    // Like: @Type(class=Foo::class, exposeInterface=true)

    // Like: @Type(class=Bar::class, implementInterface=BooBar)
}