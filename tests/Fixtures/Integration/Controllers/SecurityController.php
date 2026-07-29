<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Controllers;

use stdClass;
use TheCodingMachine\GraphQLite\Annotations\FailWith;
use TheCodingMachine\GraphQLite\Annotations\InjectUser;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\GraphQLite\Annotations\Security;
use TheCodingMachine\GraphQLite\Fixtures\PageSizeWithin;
use TheCodingMachine\GraphQLite\Fixtures\SecretIs;
use TheCodingMachine\GraphQLite\Security\SecurityRuleContext;

class SecurityController
{
    #[Query]
    #[Security("secret=='foo'", message: 'Wrong secret passed')]
    public function getSecretPhrase(string $secret): string
    {
        return 'you can see this secret only if passed parameter is "foo"';
    }

    #[Query]
    #[Security("secret=='foo'", failWith: null)]
    public function getNullableSecretPhrase(string $secret): string
    {
        return 'you can see this secret only if passed parameter is "foo"';
    }

    #[Query]
    #[Security("secret=='foo'")]
    #[FailWith(null)]
    public function getNullableSecretPhrase2(string $secret): string
    {
        return 'you can see this secret only if passed parameter is "foo"';
    }

    #[Query]
    #[Security('user && user.bar == 42')]
    public function getSecretUsingUser(): string
    {
        return 'you can see this secret only if user.bar is set to 42';
    }

    #[Query]
    #[Security("is_granted('CAN_EDIT', user) && is_logged()")]
    public function getSecretUsingIsGranted(): string
    {
        return 'you can see this secret only if user has right "CAN_EDIT"';
    }

    #[Query]
    #[Security('this.isAllowed(secret)')]
    public function getSecretUsingThis(string $secret): string
    {
        return 'you can see this secret only if isAllowed() returns true';
    }

    #[Query]
    #[Security(rule: [self::class, 'secretIsFoo'], message: 'Wrong secret passed')]
    public function getSecretPhraseByRule(string $secret): string
    {
        return 'you can see this secret only if passed parameter is "foo"';
    }

    #[Query]
    #[Security(rule: new SecretIs('foo'), failWith: null)]
    public function getNullableSecretPhraseByRule(string $secret): string
    {
        return 'you can see this secret only if passed parameter is "foo"';
    }

    /**
     * The rule's constructor argument is the limit being enforced: 100 here decides whether a
     * given "first" is allowed, and nothing else in the attribute carries that number.
     */
    #[Query]
    #[Security(rule: new PageSizeWithin(100), statusCode: 400, message: 'Page size too large')]
    public function getPagedSecret(int $first): string
    {
        return 'you can see this secret only if first is within the configured limit';
    }

    #[Query]
    #[Security(rule: [self::class, 'userBarIs42'])]
    public function getSecretUsingUserByRule(): string
    {
        return 'you can see this secret only if user.bar is set to 42';
    }

    #[Query]
    #[Security(rule: [self::class, 'canEditAndIsLogged'])]
    public function getSecretUsingIsGrantedByRule(): string
    {
        return 'you can see this secret only if user has right "CAN_EDIT"';
    }

    #[Query]
    #[Security(rule: [self::class, 'sourceAllowsSecret'])]
    public function getSecretUsingSourceByRule(string $secret): string
    {
        return 'you can see this secret only if isAllowed() returns true';
    }

    /**
     * Both checks must pass, and they are evaluated in declaration order.
     */
    #[Query]
    #[Security("secret=='foo'")]
    #[Security(rule: [self::class, 'userBarIs42'])]
    public function getSecretUsingExpressionAndRule(string $secret): string
    {
        return 'you can see this secret only if both checks pass';
    }

    #[Query]
    #[Security(rule: [self::class, 'truthyString'])]
    public function getSecretWithNonBooleanRule(): string
    {
        return 'never returned';
    }

    #[Query]
    #[Security('this.truthyValue()')]
    public function getSecretWithNonBooleanExpression(): string
    {
        return 'never returned';
    }

    #[Query]
    public function getInjectedUser(
        #[InjectUser]
        stdClass $user,
    ): int
    {
        return $user->bar;
    }

    public function isAllowed(string $secret): bool
    {
        return $secret === '42';
    }

    /** Deliberately returns a truthy non-bool, exercised through the expression path. */
    public function truthyValue(): string
    {
        return 'yes';
    }

    public static function secretIsFoo(SecurityRuleContext $context): bool
    {
        return $context->argument('secret') === 'foo';
    }

    public static function userBarIs42(SecurityRuleContext $context): bool
    {
        return $context->user !== null && $context->user->bar === 42;
    }

    public static function canEditAndIsLogged(SecurityRuleContext $context): bool
    {
        return $context->isGranted('CAN_EDIT', $context->user) && $context->isLogged();
    }

    /** Reads the source object: the rule equivalent of the `this` expression variable. */
    public static function sourceAllowsSecret(SecurityRuleContext $context): bool
    {
        return $context->source->isAllowed($context->argument('secret'));
    }

    /** Deliberately returns a truthy non-bool, to prove rules reject it rather than accepting it. */
    public static function truthyString(SecurityRuleContext $context): mixed
    {
        return 'yes';
    }
}
