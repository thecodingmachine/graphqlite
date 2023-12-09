<?php

namespace TheCodingMachine\GraphQLite\Fixtures\Types;

use TheCodingMachine\GraphQLite\Annotations\Field;

class GetterSetterType
{
    public function __construct(
        #[Field]
        public string $one = '',
        #[Field]
        public string $two = '',
        #[Field]
        public bool $three = false,
        #[Field]
        public string $four = '',
    )
    {
    }

    public function getTwo(string $arg = ''): string
    {
        return $arg;
    }

    public function setTwo(string $value): void
    {
        $this->two = $value . ' set';
    }

    public function isThree(string $arg = ''): bool
    {
        return $arg === 'foo';
    }

    private function getFour(string $arg = ''): string
    {
        throw new \RuntimeException('Should not be called');
    }

    private function setFour(string $value, string $arg): void
    {
        throw new \RuntimeException('Should not be called');
    }
}