<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Middlewares;

use Closure;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\SyntaxError;
use TheCodingMachine\GraphQLite\Annotations\Security;
use TheCodingMachine\GraphQLite\CallableResolver;
use TheCodingMachine\GraphQLite\InputField;
use TheCodingMachine\GraphQLite\InputFieldDescriptor;
use TheCodingMachine\GraphQLite\Parameters\ParameterInterface;
use TheCodingMachine\GraphQLite\QueryFieldDescriptor;
use TheCodingMachine\GraphQLite\Security\AuthenticationServiceInterface;
use TheCodingMachine\GraphQLite\Security\AuthorizationServiceInterface;
use TheCodingMachine\GraphQLite\Security\SecurityRuleContext;
use Throwable;

use function array_combine;
use function array_keys;
use function array_slice;
use function count;
use function is_bool;

/**
 * A field input middleware that reads "Security" Symfony annotations.
 * it is the equivalent to the SecurityFieldMiddleware.
 */
class SecurityInputFieldMiddleware implements InputFieldMiddlewareInterface
{
    /**
     * Variable names getVariables() always supplies, regardless of the field's own parameters.
     *
     * Kept in step with getVariables() by testExpressionCanReferenceEveryProvidedVariable(); they cannot
     * be derived from it, because building the real bag would call the authentication service at
     * schema-build time.
     */
    private const VARIABLE_NAMES = ['user', 'this', 'authorizationService', 'authenticationService'];

    public function __construct(
        private readonly ExpressionLanguage $language,
        private readonly AuthenticationServiceInterface $authenticationService,
        private readonly AuthorizationServiceInterface $authorizationService,
        private readonly CallableResolver $callableResolver,
    ) {
    }

    public function process(InputFieldDescriptor $inputFieldDescriptor, InputFieldHandlerInterface $inputFieldHandler): InputField|null
    {
        $annotations = $inputFieldDescriptor->getMiddlewareAnnotations();
        /** @var Security[] $securityAnnotations */
        $securityAnnotations = $annotations->getAnnotationsByType(Security::class);

        if (empty($securityAnnotations)) {
            return $inputFieldHandler->handle($inputFieldDescriptor);
        }

        // Normalized once, at schema build. See the sibling SecurityFieldMiddleware.
        $this->lintExpressions($securityAnnotations, $inputFieldDescriptor->getParameters(), $inputFieldDescriptor);
        $rules = $this->normalizeRules($securityAnnotations);

        $resolver = $inputFieldDescriptor->getResolver();
        $originalResolver = $inputFieldDescriptor->getOriginalResolver();

        $parameters = $inputFieldDescriptor->getParameters();
        // InputField::fromFieldDescriptor prepends a SourceParameter to the parameters list when
        // `injectSource` is true, but that injection happens AFTER this middleware runs. See the
        // sibling SecurityFieldMiddleware for the detailed comment.
        $injectSource = $inputFieldDescriptor->isInjectSource();

        $inputFieldDescriptor = $inputFieldDescriptor->withResolver(function (object|null $source, ...$args) use ($originalResolver, $securityAnnotations, $rules, $resolver, $parameters, $inputFieldDescriptor, $injectSource) {
            $executionSource = $injectSource ? $source : $originalResolver->executionSource($source);
            $arguments = $this->getArguments($args, $parameters, $injectSource);

            foreach ($securityAnnotations as $index => $annotation) {
                $rule = $rules[$index] ?? null;

                if ($rule !== null) {
                    $authorized = $rule(new SecurityRuleContext(
                        $this->authenticationService->getUser(),
                        $executionSource,
                        $arguments,
                        $this->authenticationService,
                        $this->authorizationService,
                    ));

                    if (! is_bool($authorized)) {
                        throw NonBooleanSecurityResultException::fromRule($annotation->getRule(), $inputFieldDescriptor, $authorized);
                    }
                } else {
                    $expression = $annotation->getExpression();

                    try {
                        $authorized = $this->language->evaluate($expression, $this->getVariables($executionSource, $arguments));
                    } catch (Throwable $e) {
                        throw BadExpressionInSecurityException::wrapException($e, $inputFieldDescriptor);
                    }

                    if (! is_bool($authorized)) {
                        throw NonBooleanSecurityResultException::fromExpression($expression, $inputFieldDescriptor, $authorized);
                    }
                }

                if (! $authorized) {
                    throw new MissingAuthorizationException($annotation->getMessage(), $annotation->getStatusCode());
                }
            }

            return $resolver($source, ...$args);
        });

        return $inputFieldHandler->handle($inputFieldDescriptor);
    }

    /**
     * Parses every expression while the schema is built.
     *
     * Without this a malformed expression only fails when the field is first resolved, so a typo
     * ships and surfaces as a request-time error for whoever hits that field. The variable names
     * handed to the linter are exactly those getVariables() will supply, so what lints here is
     * what evaluates later.
     *
     * @param Security[] $annotations
     * @param array<string, ParameterInterface> $parameters
     */
    private function lintExpressions(array $annotations, array $parameters, QueryFieldDescriptor|InputFieldDescriptor $descriptor): void
    {
        $names = [...self::VARIABLE_NAMES, ...array_keys($parameters)];

        // parse(), not lint(): lint() only exists from Symfony 6.1, and the --prefer-lowest CI cells
        // resolve symfony/expression-language down to 4.4. parse() has been there since 2.4 and
        // rejects exactly the same things — bad syntax, unknown variables, unknown functions.
        //
        // Parsing initialises the parser, and an ExpressionLanguage refuses further register() calls
        // once that has happened. Parsing a clone keeps every function the consumer registered
        // visible while leaving the real instance open to registration afterwards, which is how it
        // behaved before expressions were checked at build time.
        $linter = clone $this->language;

        foreach ($annotations as $annotation) {
            if (! $annotation->hasExpression()) {
                continue;
            }

            $expression = $annotation->getExpression();

            try {
                $linter->parse($expression, $names);
            } catch (SyntaxError $e) {
                throw BadExpressionInSecurityException::fromSyntaxError($e, $descriptor, $expression);
            }
        }
    }

    /**
     * @param Security[] $annotations
     *
     * @return array<int, Closure>
     */
    private function normalizeRules(array $annotations): array
    {
        $rules = [];

        foreach ($annotations as $index => $annotation) {
            $rule = $annotation->getRule();

            if ($rule === null) {
                continue;
            }

            [$rules[$index]] = $this->callableResolver->resolve($rule);
        }

        return $rules;
    }

    /**
     * @param array<int|string, mixed> $args
     * @param array<string, ParameterInterface> $parameters
     *
     * @return array<string, mixed>
     */
    private function getArguments(array $args, array $parameters, bool $injectSource): array
    {
        if ($injectSource && count($args) > count($parameters)) {
            $args = array_slice($args, 1);
        }

        $argsName = array_keys($parameters);

        return $argsName ? array_combine($argsName, $args) : [];
    }

    /**
     * @param array<string, mixed> $arguments
     *
     * @return array<string, mixed>
     */
    private function getVariables(object|null $source, array $arguments): array
    {
        $variables = [
            // If a user is not logged, we provide an empty user object to make usage easier
            'user' => $this->authenticationService->getUser(),
            'authorizationService' => $this->authorizationService, // Used by the is_granted expression language function.
            'authenticationService' => $this->authenticationService, // Used by the is_logged expression language function.
            'this' => $source,
        ];

        return $variables + $arguments;
    }
}
