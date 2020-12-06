<?php


namespace TheCodingMachine\GraphQLite;

interface ClassResolver
{
    public function __invoke(array $controllers): iterable;
}