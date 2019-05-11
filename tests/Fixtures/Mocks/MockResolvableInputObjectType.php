<?php


namespace TheCodingMachine\GraphQLite\Fixtures\Mocks;


use BadMethodCallException;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use TheCodingMachine\GraphQLite\Types\ResolvableMutableInputInterface;

class MockResolvableInputObjectType extends InputObjectType implements ResolvableMutableInputInterface
{

    public function freeze(): void
    {
        //throw new BadMethodCallException('Unauthorized call to freeze in Mock object');
    }

    public function getStatus(): string
    {
        throw new BadMethodCallException('Unauthorized call to getStatus in Mock object');
    }

    public function addFields(callable $fields): void
    {
        throw new BadMethodCallException('Unauthorized call to addFields in Mock object');
    }

    /**
     * Resolves the arguments into an object.
     *
     * @param array $args
     * @return object
     */
    public function resolve($source, array $args, $context, ResolveInfo $resolveInfo)
    {
        throw new BadMethodCallException('Unauthorized call to resolve in Mock object');
    }
}