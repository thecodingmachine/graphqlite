<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Middlewares;

use Closure;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\OutputType;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\SyntaxError;
use TheCodingMachine\GraphQLite\Annotations\FailWith;
use TheCodingMachine\GraphQLite\Annotations\Security;
use TheCodingMachine\GraphQLite\CallableResolver;
use TheCodingMachine\GraphQLite\InputFieldDescriptor;
use TheCodingMachine\GraphQLite\Parameters\ParameterInterface;
use TheCodingMachine\GraphQLite\QueryFieldDescriptor;
use TheCodingMachine\GraphQLite\Security\AuthenticationServiceInterface;
use TheCodingMachine\GraphQLite\Security\AuthorizationServiceInterface;
use TheCodingMachine\GraphQLite\Security\SecurityRuleContext;
use TheCodingMachine\GraphQLite\Security\SecurityRuleMessageInterface;
use Throwable;

use function array_combine;
use function array_keys;
use function array_slice;
use function assert;
use function count;
use function is_bool;

/**
 * A field middleware that reads "Security" Symfony annotations.
 */
class SecurityFieldMiddleware implements FieldMiddlewareInterface
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

        // Rules are normalized to a Closure here, once, rather than per request: an unresolvable
        // callable then fails at schema build with a class and method name in the message, and the
        // resolver below never has to branch on the shape the attribute was given.
        $this->lintExpressions($securityAnnotations, $queryFieldDescriptor->getParameters(), $queryFieldDescriptor);
        $rules = $this->normalizeRules($securityAnnotations);

        $resolver = $queryFieldDescriptor->getResolver();
        $originalResolver = $queryFieldDescriptor->getOriginalResolver();

        $parameters = $queryFieldDescriptor->getParameters();
        // QueryField::fromFieldDescriptor prepends a SourceParameter to the parameters list when
        // `injectSource` is true, but that injection happens AFTER this middleware runs. At resolver
        // invocation time the runtime $args therefore includes the source as its first element while
        // $parameters captured here does not. Track the flag so getVariables() can strip the leading
        // source arg before zipping args to parameter names.
        $injectSource = $queryFieldDescriptor->isInjectSource();

        $queryFieldDescriptor = $queryFieldDescriptor->withResolver(function (object|null $source, ...$args) use ($originalResolver, $securityAnnotations, $rules, $resolver, $failWith, $parameters, $queryFieldDescriptor, $injectSource) {
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

                    // An authorization check silently treating 0, '' or null as denial and the
                    // string 'false' as permission is not a behavior worth carrying over to rules.
                    if (! is_bool($authorized)) {
                        throw NonBooleanSecurityResultException::fromRule($annotation->getRule(), $queryFieldDescriptor, $authorized);
                    }
                } else {
                    $expression = $annotation->getExpression();

                    try {
                        $authorized = $this->language->evaluate($expression, $this->getVariables($executionSource, $arguments));
                    } catch (Throwable $e) {
                        throw BadExpressionInSecurityException::wrapException($e, $queryFieldDescriptor);
                    }

                    if (! is_bool($authorized)) {
                        throw NonBooleanSecurityResultException::fromExpression($expression, $queryFieldDescriptor, $authorized);
                    }
                }

                if (! $authorized) {
                    if ($annotation->isFailWithSet()) {
                        return $annotation->getFailWith();
                    }
                    if ($failWith !== null) {
                        return $failWith->getValue();
                    }

                    throw new MissingAuthorizationException($this->resolveMessage($annotation), $annotation->getStatusCode());
                }
            }

            return $resolver($source, ...$args);
        });

        return $fieldHandler->handle($queryFieldDescriptor);
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
     * Turns every rule-bearing annotation into a Closure, keyed by its position in $annotations.
     *
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
     * The message a denied field is reported with.
     *
     * A message written on the attribute is the field author's decision about this field, so it
     * always wins. Failing that, a rule stating its own message supplies one; failing that too,
     * getMessage() returns "Access denied.", which keeps the default written in a single place.
     *
     * The rule is read back from the annotation rather than from the normalized Closure. Rules are
     * normalized to Closures at schema build, and a Closure can be called but not asked anything;
     * the annotation still holds the rule exactly as the attribute wrote it, which is where an
     * invokable rule object remains reachable. Nothing about the normalization needs to change.
     */
    private function resolveMessage(Security $annotation): string
    {
        if (! $annotation->hasMessage()) {
            $rule = $annotation->getRule();

            if ($rule instanceof SecurityRuleMessageInterface) {
                return $rule->getRefusalMessage();
            }
        }

        return $annotation->getMessage();
    }

    /**
     * The field's resolved PHP arguments, keyed by parameter name.
     *
     * @param array<int|string, mixed> $args
     * @param array<string, ParameterInterface> $parameters
     *
     * @return array<string, mixed>
     */
    private function getArguments(array $args, array $parameters, bool $injectSource): array
    {
        // Strip the source arg prepended by QueryField::fromFieldDescriptor so the remaining
        // user-supplied args line up positionally with the captured parameter names. The source
        // is always exposed separately, so there's no loss of information.
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
