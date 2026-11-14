<?php


namespace TheCodingMachine\GraphQLite\Tests\Fixtures\AnnotatedInterfaces\Types;

interface ParentInterface extends ParentParentInterface
{
    public function getParentValue(): string;
}