<?php


namespace TheCodingMachine\GraphQLite\Tests\Fixtures\AnnotatedInterfaces\Types;


class NotAnnotatedQux implements QuxInterface
{
    public function getQux(): string
    {
        return 'qux';
    }
}