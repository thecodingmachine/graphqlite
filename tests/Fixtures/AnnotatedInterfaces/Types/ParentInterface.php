<?php


namespace TheCodingMachine\GraphQLite\Fixtures\AnnotatedInterfaces\Types;

interface ParentInterface extends ParentParentInterface
{
    public function getParentValue(): string;
}