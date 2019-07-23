<?php


namespace TheCodingMachine\GraphQLite\Fixtures\AnnotatedInterfaces\Types;


class NotAnnotatedQux implements QuxInterface
{
    public function getQux(): string
    {
        return 'qux';
    }
}