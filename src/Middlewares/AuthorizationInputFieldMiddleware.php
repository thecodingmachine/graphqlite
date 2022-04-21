<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Middlewares;

use GraphQL\Type\Definition\InputObjectField;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\OutputType;
use TheCodingMachine\GraphQLite\Annotations\Exceptions\IncompatibleAnnotationsException;
use TheCodingMachine\GraphQLite\Annotations\FailWith;
use TheCodingMachine\GraphQLite\Annotations\HideIfUnauthorized;
use TheCodingMachine\GraphQLite\Annotations\Logged;
use TheCodingMachine\GraphQLite\Annotations\Right;
use TheCodingMachine\GraphQLite\InputField;
use TheCodingMachine\GraphQLite\InputFieldDescriptor;
use TheCodingMachine\GraphQLite\Security\AuthenticationServiceInterface;
use TheCodingMachine\GraphQLite\Security\AuthorizationServiceInterface;
use Webmozart\Assert\Assert;

use function assert;

/**
 * Middleware in charge of managing "Logged" and "Right" annotations.
 */
class AuthorizationInputFieldMiddleware implements InputFieldMiddlewareInterface
{
    /** @var AuthenticationServiceInterface */
    private $authenticationService;
    /** @var AuthorizationServiceInterface */
    private $authorizationService;

    public function __construct(
        AuthenticationServiceInterface $authenticationService,
        AuthorizationServiceInterface $authorizationService
    ) {
        $this->authenticationService = $authenticationService;
        $this->authorizationService = $authorizationService;
    }

    /**
     * @throws MissingAuthorizationException
     */
    public function process(InputFieldDescriptor $inputFieldDescriptor, InputFieldHandlerInterface $inputFieldHandler): ?InputObjectField
    {
        $annotations = $inputFieldDescriptor->getMiddlewareAnnotations();

        $loggedAnnotation = $annotations->getAnnotationByType(Logged::class);
        assert($loggedAnnotation === null || $loggedAnnotation instanceof Logged);
        $rightAnnotation = $annotations->getAnnotationByType(Right::class);
        assert($rightAnnotation === null || $rightAnnotation instanceof Right);

//        $failWith = $annotations->getAnnotationByType(FailWith::class);
//        assert($failWith === null || $failWith instanceof FailWith);

        // If the failWith value is null and the return type is non nullable, we must set it to nullable.
//        $type = $inputFieldDescriptor->getType();
//        if ($failWith !== null && $type instanceof NonNull && $failWith->getValue() === null) {
//            $type = $type->getWrappedType();
//            Assert::isInstanceOf($type, OutputType::class);
//            $inputFieldDescriptor->setType($type);
//        }
        if ($this->isAuthorized($loggedAnnotation, $rightAnnotation)) {
            return $inputFieldHandler->handle($inputFieldDescriptor);
        }

        $hideIfUnauthorized = $annotations->getAnnotationByType(HideIfUnauthorized::class);
        assert($hideIfUnauthorized instanceof HideIfUnauthorized || $hideIfUnauthorized === null);

//        if ($failWith !== null && $hideIfUnauthorized !== null) {
//            throw IncompatibleAnnotationsException::cannotUseFailWithAndHide();
//        }
//
//        if ($failWith !== null) {
//            $failWithValue = $failWith->getValue();
//
//            return InputField::alwaysReturn($inputFieldDescriptor, $failWithValue);
//        }

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
