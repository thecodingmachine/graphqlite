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
        private readonly AuthenticationServiceInterface $authenticationService,
        private readonly AuthorizationServiceInterface $authorizationService,
    )
    {
    }

    /** @throws MissingAuthorizationException */
    public function process(InputFieldDescriptor $inputFieldDescriptor, InputFieldHandlerInterface $inputFieldHandler): InputField|null
    {
        $annotations = $inputFieldDescriptor->getMiddlewareAnnotations();

        $loggedAnnotation = $annotations->getAnnotationByType(Logged::class);
        assert($loggedAnnotation === null || $loggedAnnotation instanceof Logged);
        $rightAnnotation = $annotations->getAnnotationByType(Right::class);
        assert($rightAnnotation === null || $rightAnnotation instanceof Right);

        // Avoid wrapping resolver callback when no annotations are specified.
        if (! $loggedAnnotation && ! $rightAnnotation) {
            return $inputFieldHandler->handle($inputFieldDescriptor);
        }

        $hideIfUnauthorized = $annotations->getAnnotationByType(HideIfUnauthorized::class);
        assert($hideIfUnauthorized instanceof HideIfUnauthorized || $hideIfUnauthorized === null);

        if ($hideIfUnauthorized !== null && ! $this->isAuthorized($loggedAnnotation, $rightAnnotation)) {
            return null;
        }

        $resolver = $inputFieldDescriptor->getResolver();

        $inputFieldDescriptor->setResolver(function (...$args) use ($rightAnnotation, $loggedAnnotation, $resolver) {
            if ($this->isAuthorized($loggedAnnotation, $rightAnnotation)) {
                return $resolver(...$args);
            }

            if ($loggedAnnotation !== null && ! $this->authenticationService->isLogged()) {
                throw MissingAuthorizationException::unauthorized();
            }

            throw MissingAuthorizationException::forbidden();
        });

        return $inputFieldHandler->handle($inputFieldDescriptor);
    }

    /**
     * Checks the @Logged and @Right annotations.
     */
    private function isAuthorized(Logged|null $loggedAnnotation, Right|null $rightAnnotation): bool
    {
        if ($loggedAnnotation !== null && ! $this->authenticationService->isLogged()) {
            return false;
        }

        return $rightAnnotation === null || $this->authorizationService->isAllowed($rightAnnotation->getName());
    }
}
