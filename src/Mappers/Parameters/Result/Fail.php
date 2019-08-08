<?php

namespace TheCodingMachine\GraphQLite\Mappers\Parameters\Result;

interface Fail extends Result
{
    /**
     * @return string
     */
    public function getMessage(): string;
}