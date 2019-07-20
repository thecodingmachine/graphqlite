<?php


namespace TheCodingMachine\GraphQLite\Fixtures\AnnotatedInterfaces\Types;


use TheCodingMachine\GraphQLite\Annotations\Field;

interface WizzInterface
{
    /**
     * @Field()
     */
    public function getWizz(): string;
}