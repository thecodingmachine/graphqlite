<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Middlewares;

use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\OutputType;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use TheCodingMachine\GraphQLite\Annotations\FailWith;
use TheCodingMachine\GraphQLite\Annotations\Security;
use TheCodingMachine\GraphQLite\Parameters\ParameterInterface;
use TheCodingMachine\GraphQLite\QueryFieldDescriptor;
use TheCodingMachine\GraphQLite\Security\AuthenticationServiceInterface;
use TheCodingMachine\GraphQLite\Security\AuthorizationServiceInterface;
use Throwable;

use function array_combine;
use function array_keys;
use function array_slice;
use function assert;
use function count;

/**
 * A field middleware that reads "Security" Symfony annotations.
 */
class SecurityFieldMiddleware implements FieldMiddlewareInterface
{
    public function __construct(
        private readonly ExpressionLanguage $language,
        private readonly AuthenticationServiceInterface $authenticationService,
        private readonly AuthorizationServiceInterface $authorizationService,
    ) {
    }

    public function process(
        QueryFieldDescriptor $queryFieldDescriptor,
        FieldHandlerInterface $fieldHandler,
    ): FieldDefinition|null
    {
        $annotations = $queryFieldDescriptor->getMiddlewareAnnotations();
        /** @var Security[] $securityAnnotations */
        $securityAnnotations = $annotations->getAnnotationsByType(Security::class);

        if (empty($securityAnnotations)) {
            return $fieldHandler->handle($queryFieldDescriptor);
        }

        $failWith = $annotations->getAnnotationByType(FailWith::class);

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
                assert($type instanceof OutputType);
                $queryFieldDescriptor = $queryFieldDescriptor->withType($type);
            }
        }

        $resolver = $queryFieldDescriptor->getResolver();
        $originalResolver = $queryFieldDescriptor->getOriginalResolver();

        $parameters = $queryFieldDescriptor->getParameters();
        // QueryField::fromFieldDescriptor prepends a SourceParameter to the parameters list when
        // `injectSource` is true, but that injection happens AFTER this middleware runs. At resolver
        // invocation time the runtime $args therefore includes the source as its first element while
        // $parameters captured here does not. Track the flag so getVariables() can strip the leading
        // source arg before zipping args to parameter names.
        $injectSource = $queryFieldDescriptor->isInjectSource();

        $queryFieldDescriptor = $queryFieldDescriptor->withResolver(function (object|null $source, ...$args) use ($originalResolver, $securityAnnotations, $resolver, $failWith, $parameters, $queryFieldDescriptor, $injectSource) {
            $variables = $this->getVariables($args, $parameters, $originalResolver->executionSource($source), $injectSource);

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

            return $resolver($source, ...$args);
        });

        return $fieldHandler->handle($queryFieldDescriptor);
    }

    /**
     * @param array<int|string, mixed> $args
     * @param array<string, ParameterInterface> $parameters
     *
     * @return array<string, mixed>
     */
    private function getVariables(array $args, array $parameters, object|null $source, bool $injectSource = false): array
    {
        $variables = [
            // If a user is not logged, we provide an empty user object to make usage easier
            'user' => $this->authenticationService->getUser(),
            'authorizationService' => $this->authorizationService, // Used by the is_granted expression language function.
            'authenticationService' => $this->authenticationService, // Used by the is_logged expression language function.
            'this' => $source,
        ];

        // Strip the source arg prepended by QueryField::fromFieldDescriptor so the remaining
        // user-supplied args line up positionally with the captured parameter names. The source
        // is always exposed via `this`, so there's no loss of information for the expression.
        if ($injectSource && count($args) > count($parameters)) {
            $args = array_slice($args, 1);
        }

        $argsName = array_keys($parameters);
        $argsByName = $argsName ? array_combine($argsName, $args) : [];

        return $variables + $argsByName;
    }
}
