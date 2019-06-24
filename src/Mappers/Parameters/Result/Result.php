<?php


namespace TheCodingMachine\GraphQLite\Mappers\Parameters\Result;


interface Result
{
    /**
     * If the result is an error, an exception is thrown
     */
    public function throwIfError(): void;
}
