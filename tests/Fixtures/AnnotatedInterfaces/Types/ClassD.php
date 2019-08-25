<?php


namespace TheCodingMachine\GraphQLite\Fixtures\AnnotatedInterfaces\Types;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Type;

/**
 * @Type()
 */
class ClassD extends ClassC
{
    /**
     * @Field()
     */
    public function getClassD(): string
    {
        return 'classD';
    }
}