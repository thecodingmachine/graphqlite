<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Parameters;

use GraphQL\Type\Definition\ResolveInfo;

/**
 * Instances of ParameterInterface represent a single PHP parameter in a Query/Mutation/Field/Factory.
 *
 * @unstable See https://graphqlite.thecodingmachine.io/docs/semver.html
 */
interface ParameterInterface
{
    /**
     * @param array<string, mixed> $args
     * @param mixed                $context
     *
     * @return mixed
     */
    public function resolve(?object $source, array $args, $context, ResolveInfo $info);
}
