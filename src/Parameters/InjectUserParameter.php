<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Parameters;

use GraphQL\Type\Definition\ResolveInfo;
use TheCodingMachine\GraphQLite\Middlewares\MissingAuthorizationException;
use TheCodingMachine\GraphQLite\Security\AuthenticationServiceInterface;

/**
 * A parameter filled from the current user.
 */
class InjectUserParameter implements ParameterInterface
{
    public function __construct(
        private readonly AuthenticationServiceInterface $authenticationService,
        private readonly bool $optional,
    )
    {
    }

    /** @param array<string, mixed> $args */
    public function resolve(object|null $source, array $args, mixed $context, ResolveInfo $info): object|null
    {
        $user = $this->authenticationService->getUser();

        // If user is required but wasn't provided, we'll throw unauthorized error the same way #[Logged] does.
        if (! $user && ! $this->optional) {
            throw MissingAuthorizationException::unauthorized();
        }

        return $user;
    }
}
