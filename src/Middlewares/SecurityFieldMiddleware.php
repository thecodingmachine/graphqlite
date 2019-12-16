<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Middlewares;

use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\OutputType;
use Psr\Log\LoggerInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use TheCodingMachine\GraphQLite\Annotations\FailWith;
use TheCodingMachine\GraphQLite\Annotations\Security;
use TheCodingMachine\GraphQLite\Parameters\ParameterInterface;
use TheCodingMachine\GraphQLite\QueryFieldDescriptor;
use TheCodingMachine\GraphQLite\Security\AuthenticationServiceInterface;
use TheCodingMachine\GraphQLite\Security\AuthorizationServiceInterface;
use Throwable;
use Webmozart\Assert\Assert;
use function array_combine;
use function array_keys;
use function array_merge;
use function assert;

/**
 * A field middleware that reads "Security" Symfony annotations.
 */
class SecurityFieldMiddleware implements FieldMiddlewareInterface
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

    public function process(QueryFieldDescriptor $queryFieldDescriptor, FieldHandlerInterface $fieldHandler): ?FieldDefinition
    {
        $annotations = $queryFieldDescriptor->getMiddlewareAnnotations();
        /** @var Security[] $securityAnnotations */
        $securityAnnotations = $annotations->getAnnotationsByType(Security::class);

        if (empty($securityAnnotations)) {
            return $fieldHandler->handle($queryFieldDescriptor);
        }

        $failWith = $annotations->getAnnotationByType(FailWith::class);
        assert($failWith instanceof FailWith || $failWith === null);

        // If the failWith value is null and the return type is non nullable, we must set it to nullable.
        $makeReturnTypeNullable = false;
        $type = $queryFieldDescriptor->getType();
        if ($type instanceof NonNull) {
            if ($failWith !== null && $failWith->getValue() === null) {
                $makeReturnTypeNullable = true;
            } else {
                foreach ($securityAnnotations as $annotation) {
                    if (! $annotation->isFailWithSet() || $annotation->getFailWith() !== null) {
                        continue;
                    }

                    $makeReturnTypeNullable = true;
                }
            }
            if ($makeReturnTypeNullable) {
                $type = $type->getWrappedType();
                Assert::isInstanceOf($type, OutputType::class);
                $queryFieldDescriptor->setType($type);
            }
        }

        $resolver = $queryFieldDescriptor->getResolver();
        $originalResolver = $queryFieldDescriptor->getOriginalResolver();

        $parameters = $queryFieldDescriptor->getParameters();

        $queryFieldDescriptor->setResolver(function (...$args) use ($securityAnnotations, $resolver, $failWith, $parameters, $queryFieldDescriptor, $originalResolver) {
            $variables = $this->getVariables($args, $parameters, $originalResolver);

            foreach ($securityAnnotations as $annotation) {
                try {
                    $authorized = $this->language->evaluate($annotation->getExpression(), $variables);
                } catch (Throwable $e) {
                    throw BadExpressionInSecurityException::wrapException($e, $queryFieldDescriptor);
                }

                if (! $authorized) {
                    if ($annotation->isFailWithSet()) {
                        return $annotation->getFailWith();
                    }
                    if ($failWith !== null) {
                        return $failWith->getValue();
                    }

                    throw new MissingAuthorizationException($annotation->getMessage(), $annotation->getStatusCode());
                }
            }

            return $resolver(...$args);
        });

        return $fieldHandler->handle($queryFieldDescriptor);
    }

    /**
     * @param array<int, mixed> $args
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

        return array_merge($argsByName, $variables);
    }
}
