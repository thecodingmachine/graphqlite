<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Middlewares;

use TheCodingMachine\GraphQLite\Annotations\HideIfUnauthorized;
use TheCodingMachine\GraphQLite\Annotations\Logged;
use TheCodingMachine\GraphQLite\Annotations\Right;
use TheCodingMachine\GraphQLite\InputField;
use TheCodingMachine\GraphQLite\InputFieldDescriptor;
use TheCodingMachine\GraphQLite\Security\AuthenticationServiceInterface;
use TheCodingMachine\GraphQLite\Security\AuthorizationServiceInterface;

use function assert;

/**
 * Middleware in charge of managing "Logged" and "Right" annotations.
 */
class AuthorizationInputFieldMiddleware implements InputFieldMiddlewareInterface
{
    public function __construct(
        private AuthenticationServiceInterface $authenticationService,
        private AuthorizationServiceInterface $authorizationService
    )
    {
    }

    /**
     * @throws MissingAuthorizationException
     */
    public function process(InputFieldDescriptor $inputFieldDescriptor, InputFieldHandlerInterface $inputFieldHandler): ?InputField
    {
        $annotations = $inputFieldDescriptor->getMiddlewareAnnotations();

        $loggedAnnotation = $annotations->getAnnotationByType(Logged::class);
        assert($loggedAnnotation === null || $loggedAnnotation instanceof Logged);
        $rightAnnotation = $annotations->getAnnotationByType(Right::class);
        assert($rightAnnotation === null || $rightAnnotation instanceof Right);

        if ($this->isAuthorized($loggedAnnotation, $rightAnnotation)) {
            return $inputFieldHandler->handle($inputFieldDescriptor);
        }

        $hideIfUnauthorized = $annotations->getAnnotationByType(HideIfUnauthorized::class);
        assert($hideIfUnauthorized instanceof HideIfUnauthorized || $hideIfUnauthorized === null);

        if ($hideIfUnauthorized !== null) {
            return null;
        }
        return InputField::unauthorizedError($inputFieldDescriptor, $loggedAnnotation !== null && ! $this->authenticationService->isLogged());
    }

    /**
     * Checks the @Logged and @Right annotations.
     */
    private function isAuthorized(?Logged $loggedAnnotation, ?Right $rightAnnotation): bool
    {
        if ($loggedAnnotation !== null && ! $this->authenticationService->isLogged()) {
            return false;
        }

        return $rightAnnotation === null || $this->authorizationService->isAllowed($rightAnnotation->getName());
    }
}
