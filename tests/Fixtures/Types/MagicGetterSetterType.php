<?php

namespace TheCodingMachine\GraphQLite\Tests\Fixtures\Types;

class MagicGetterSetterType extends GetterSetterType
{
    private string $magic;

    public function __get(string $name)
    {
        return $this->magic;
    }

    public function __call(string $name, array $arguments)
    {
        $this->magic = 'magic';

        return 'magic';
    }
}