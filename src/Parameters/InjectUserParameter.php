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
    /** @var AuthenticationServiceInterface */
    private $authenticationService;

    public function __construct(AuthenticationServiceInterface $authenticationService)
    {
        $this->authenticationService = $authenticationService;
    }

    /**
     * @param array<string, mixed> $args
     * @param mixed                $context
     *
     * @return mixed
     */
    public function resolve(?object $source, array $args, $context, ResolveInfo $info)
    {
        return $this->authenticationService->getUser();
    }
}
