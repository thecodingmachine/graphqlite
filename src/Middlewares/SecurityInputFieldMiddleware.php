<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Middlewares;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use TheCodingMachine\GraphQLite\Annotations\Security;
use TheCodingMachine\GraphQLite\InputField;
use TheCodingMachine\GraphQLite\InputFieldDescriptor;
use TheCodingMachine\GraphQLite\Parameters\ParameterInterface;
use TheCodingMachine\GraphQLite\Security\AuthenticationServiceInterface;
use TheCodingMachine\GraphQLite\Security\AuthorizationServiceInterface;
use Throwable;
use Webmozart\Assert\Assert;

use function array_combine;
use function array_keys;

/**
 * A field input middleware that reads "Security" Symfony annotations.
 * it is the equivalent to the SecurityFieldMiddleware.
 */
class SecurityInputFieldMiddleware implements InputFieldMiddlewareInterface
{
    /** @var ExpressionLanguage */
    private $language;
    /** @var AuthenticationServiceInterface */
    private $authenticationService;
    /** @var AuthorizationServiceInterface */
    private $authorizationService;

    public function __construct(ExpressionLanguage $language, AuthenticationServiceInterface $authenticationService, AuthorizationServiceInterface $authorizationService)
    {
        $this->language = $language;
        $this->authenticationService = $authenticationService;
        $this->authorizationService = $authorizationService;
    }

    public function process(InputFieldDescriptor $inputFieldDescriptor, InputFieldHandlerInterface $inputFieldHandler): ?InputField
    {
        $annotations = $inputFieldDescriptor->getMiddlewareAnnotations();
        /** @var Security[] $securityAnnotations */
        $securityAnnotations = $annotations->getAnnotationsByType(Security::class);

        if (empty($securityAnnotations)) {
            return $inputFieldHandler->handle($inputFieldDescriptor);
        }

        $resolver = $inputFieldDescriptor->getResolver();
        $originalResolver = $inputFieldDescriptor->getOriginalResolver();

        $parameters = $inputFieldDescriptor->getParameters();

        $inputFieldDescriptor->setResolver(function (...$args) use ($securityAnnotations, $resolver, $parameters, $inputFieldDescriptor, $originalResolver) {
            $variables = $this->getVariables($args, $parameters, $originalResolver);

            foreach ($securityAnnotations as $annotation) {
                try {
                    $authorized = $this->language->evaluate($annotation->getExpression(), $variables);
                } catch (Throwable $e) {
                    throw BadExpressionInSecurityException::wrapException($e, $inputFieldDescriptor);
                }

                if (! $authorized) {
                    throw new MissingAuthorizationException($annotation->getMessage(), $annotation->getStatusCode());
                }
            }

            return $resolver(...$args);
        });

        return $inputFieldHandler->handle($inputFieldDescriptor);
    }

    /**
     * @param array<int|string, mixed> $args
     * @param array<string, ParameterInterface> $parameters
     *
     * @return array<string, mixed>
     */
    private function getVariables(array $args, array $parameters, ResolverInterface $callable): array
    {
        $variables = [
            // If a user is not logged, we provide an empty user object to make usage easier
            'user' => $this->authenticationService->getUser(),
            'authorizationService' => $this->authorizationService, // Used by the is_granted expression language function.
            'authenticationService' => $this->authenticationService, // Used by the is_logged expression language function.
            'this' => $callable->getObject(),
        ];

        $argsName = array_keys($parameters);
        $argsByName = array_combine($argsName, $args);
        Assert::isArray($argsByName);

        return $variables + $argsByName;
    }
}
