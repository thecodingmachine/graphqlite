<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Parameters;

use GraphQL\Type\Definition\ResolveInfo;
use TheCodingMachine\GraphQLite\Security\AuthenticationServiceInterface;

/**
 * A parameter filled from the current user.
 */
class InjectUserParameter implements ParameterInterface
{
    public function __construct(private AuthenticationServiceInterface $authenticationService)
    {
    }

    /**
     * @param array<string, mixed> $args
     */
    public function resolve(?object $source, array $args, mixed $context, ResolveInfo $info): mixed
    {
        return $this->authenticationService->getUser();
    }
}
