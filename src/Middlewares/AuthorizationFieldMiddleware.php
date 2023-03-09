<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Middlewares;

use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\OutputType;
use TheCodingMachine\GraphQLite\Annotations\Exceptions\IncompatibleAnnotationsException;
use TheCodingMachine\GraphQLite\Annotations\FailWith;
use TheCodingMachine\GraphQLite\Annotations\HideIfUnauthorized;
use TheCodingMachine\GraphQLite\Annotations\Logged;
use TheCodingMachine\GraphQLite\Annotations\Right;
use TheCodingMachine\GraphQLite\QueryFieldDescriptor;
use TheCodingMachine\GraphQLite\Security\AuthenticationServiceInterface;
use TheCodingMachine\GraphQLite\Security\AuthorizationServiceInterface;

use function assert;

/**
 * Middleware in charge of managing "Logged" and "Right" annotations.
 */
class AuthorizationFieldMiddleware implements FieldMiddlewareInterface
{
    public function __construct(
        private AuthenticationServiceInterface $authenticationService,
        private AuthorizationServiceInterface $authorizationService,
    ) {
    }

    public function process(QueryFieldDescriptor $queryFieldDescriptor, FieldHandlerInterface $fieldHandler): FieldDefinition|null
    {
        $annotations = $queryFieldDescriptor->getMiddlewareAnnotations();

        $loggedAnnotation = $annotations->getAnnotationByType(Logged::class);
        assert($loggedAnnotation === null || $loggedAnnotation instanceof Logged);
        $rightAnnotation = $annotations->getAnnotationByType(Right::class);
        assert($rightAnnotation === null || $rightAnnotation instanceof Right);

        // Avoid wrapping resolver callback when no annotations are specified.
        if (! $loggedAnnotation && ! $rightAnnotation) {
            return $fieldHandler->handle($queryFieldDescriptor);
        }

        $failWith = $annotations->getAnnotationByType(FailWith::class);
        assert($failWith === null || $failWith instanceof FailWith);
        $hideIfUnauthorized = $annotations->getAnnotationByType(HideIfUnauthorized::class);
        assert($hideIfUnauthorized instanceof HideIfUnauthorized || $hideIfUnauthorized === null);

        if ($failWith !== null && $hideIfUnauthorized !== null) {
            throw IncompatibleAnnotationsException::cannotUseFailWithAndHide();
        }

        // If the failWith value is null and the return type is non-nullable, we must set it to nullable.
        $type = $queryFieldDescriptor->getType();
        if ($failWith !== null && $type instanceof NonNull && $failWith->getValue() === null) {
            $type = $type->getWrappedType();
            assert($type instanceof OutputType);
            $queryFieldDescriptor->setType($type);
        }

        // When using the same Schema instance for multiple subsequent requests, this middleware will only
        // get called once, meaning #[HideIfUnauthorized] only works when Schema is used for a single request
        // and then discarded. This check is to keep the latter case working.
        if ($hideIfUnauthorized !== null && ! $this->isAuthorized($loggedAnnotation, $rightAnnotation)) {
            return null;
        }

        $resolver = $queryFieldDescriptor->getResolver();

        $queryFieldDescriptor->setResolver(function (...$args) use ($rightAnnotation, $loggedAnnotation, $failWith, $resolver) {
            if ($this->isAuthorized($loggedAnnotation, $rightAnnotation)) {
                return $resolver(...$args);
            }

            if ($failWith !== null) {
                return $failWith->getValue();
            }

            if ($loggedAnnotation !== null && ! $this->authenticationService->isLogged()) {
                throw MissingAuthorizationException::unauthorized();
            }

            throw MissingAuthorizationException::forbidden();
        });

        return $fieldHandler->handle($queryFieldDescriptor);
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
