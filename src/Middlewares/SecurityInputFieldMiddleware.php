<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Middlewares;

use GraphQL\Type\Definition\InputObjectField;
use Psr\Log\LoggerInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use TheCodingMachine\GraphQLite\Annotations\Security;
use TheCodingMachine\GraphQLite\Parameters\ParameterInterface;
use TheCodingMachine\GraphQLite\InputFieldDescriptor;
use TheCodingMachine\GraphQLite\Security\AuthenticationServiceInterface;
use TheCodingMachine\GraphQLite\Security\AuthorizationServiceInterface;
use Throwable;
use Webmozart\Assert\Assert;

use function array_combine;
use function array_keys;
use function assert;

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
    /** @var LoggerInterface|null */
    /*private $logger;*/
    /** @var AuthorizationServiceInterface */
    private $authorizationService;

    public function __construct(ExpressionLanguage $language, AuthenticationServiceInterface $authenticationService, AuthorizationServiceInterface $authorizationService/*, ?LoggerInterface $logger = null*/)
    {
        $this->language = $language;
        $this->authenticationService = $authenticationService;
        $this->authorizationService = $authorizationService;
        /*$this->logger = $logger;*/
    }

    public function process(InputFieldDescriptor $inputFieldDescriptor, InputFieldHandlerInterface $inputFieldHandler): ?InputObjectField
    {
        $annotations = $inputFieldDescriptor->getMiddlewareAnnotations();
        /** @var Security[] $securityAnnotations */
        $securityAnnotations = $annotations->getAnnotationsByType(Security::class);

        if (empty($securityAnnotations)) {
            return $inputFieldHandler->handle($inputFieldDescriptor);
        }

//        $failWith = $annotations->getAnnotationByType(FailWith::class);
//        assert($failWith instanceof FailWith || $failWith === null);
//
//        // If the failWith value is null and the return type is non nullable, we must set it to nullable.
//        $makeReturnTypeNullable = false;
//        $type = $inputFieldDescriptor->getType();
//        if ($type instanceof NonNull) {
//            if ($failWith !== null && $failWith->getValue() === null) {
//                $makeReturnTypeNullable = true;
//            } else {
//                foreach ($securityAnnotations as $annotation) {
//                    if (! $annotation->isFailWithSet() || $annotation->getFailWith() !== null) {
//                        continue;
//                    }
//
//                    $makeReturnTypeNullable = true;
//                }
//            }
//            if ($makeReturnTypeNullable) {
//                $type = $type->getWrappedType();
//                Assert::isInstanceOf($type, OutputType::class);
//                $inputFieldDescriptor->setType($type);
//            }
//        }

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

        /*if ($diff = array_intersect(array_keys($variables), array_keys($argsName))) {
            foreach ($diff as $key => $variableName) {
                if ($variables[$variableName] !== $argsByName[$variableName]) {
                    continue;
                }

                unset($diff[$key]);
            }

            if ($diff) {
                $singular = count($diff) === 1;
                if ($this->logger !== null) {
                    $this->logger->warning(sprintf('Controller argument%s "%s" collided with the built-in security expression variables. The built-in value%s are being used for the @Security expression.', $singular ? '' : 's', implode('", "', $diff), $singular ? 's' : ''));
                }
            }
        }*/

        return $variables + $argsByName;
    }
}
