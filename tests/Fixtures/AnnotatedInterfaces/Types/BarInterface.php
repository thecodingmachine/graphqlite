<?php


namespace TheCodingMachine\GraphQLite\Fixtures\AnnotatedInterfaces\Types;


use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Type;

/**
 * @Type()
 */
interface BarInterface
{
    /**
     * @Field()
     */
    public function getBar(): string;
}