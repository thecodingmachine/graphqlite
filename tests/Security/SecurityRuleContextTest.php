<?php

namespace TheCodingMachine\GraphQLite\Security;

use PHPUnit\Framework\TestCase;
use stdClass;

use function array_key_exists;

class SecurityRuleContextTest extends TestCase
{
    public function testExposesUserSourceAndArguments(): void
    {
        $user = new stdClass();
        $source = new stdClass();

        $context = $this->context($user, $source, ['first' => 10, 'search' => null]);

        self::assertSame($user, $context->user);
        self::assertSame($source, $context->source);
        self::assertSame(['first' => 10, 'search' => null], $context->arguments);
    }

    /**
     * The contract is what a rule may type-hint, so the context GraphQLite actually builds has to
     * satisfy it.
     */
    public function testSatisfiesTheContract(): void
    {
        self::assertInstanceOf(SecurityRuleContextInterface::class, $this->context(null, null, []));
    }

    /**
     * The accessors exist because an interface cannot declare properties on PHP 8.2, so they must
     * report exactly what the readonly properties hold. A rule reads one or the other and must not
     * be able to tell which it got.
     */
    public function testAccessorsAgreeWithTheReadonlyProperties(): void
    {
        $user = new stdClass();
        $source = new stdClass();

        $context = $this->context($user, $source, ['first' => 10, 'search' => null]);

        self::assertSame($context->user, $context->getUser());
        self::assertSame($context->source, $context->getSource());
        self::assertSame($context->arguments, $context->getArguments());
    }

    public function testArgumentReadsByName(): void
    {
        $context = $this->context(null, null, ['first' => 10]);

        self::assertSame(10, $context->argument('first'));
    }

    public function testArgumentIsNullForAnAbsentArgument(): void
    {
        $context = $this->context(null, null, []);

        self::assertNull($context->argument('missing'));
    }

    public function testHasArgumentDistinguishesNullFromAbsent(): void
    {
        $context = $this->context(null, null, ['explicitlyNull' => null]);

        self::assertTrue($context->hasArgument('explicitlyNull'));
        self::assertFalse($context->hasArgument('missing'));
        self::assertNull($context->argument('explicitlyNull'));
    }

    public function testIsGrantedDelegatesToTheAuthorizationService(): void
    {
        $subject = new stdClass();

        $authorization = $this->createMock(AuthorizationServiceInterface::class);
        $authorization->expects($this->once())
            ->method('isAllowed')
            ->with('DOCUMENT_READ', $subject)
            ->willReturn(true);

        $context = new SecurityRuleContext(
            null,
            null,
            [],
            $this->createMock(AuthenticationServiceInterface::class),
            $authorization,
        );

        self::assertTrue($context->isGranted('DOCUMENT_READ', $subject));
    }

    public function testIsGrantedPassesNoSubjectWhenNoneIsGiven(): void
    {
        $authorization = $this->createMock(AuthorizationServiceInterface::class);
        $authorization->expects($this->once())
            ->method('isAllowed')
            ->with('ROLE_ADMIN', null)
            ->willReturn(false);

        $context = new SecurityRuleContext(
            null,
            null,
            [],
            $this->createMock(AuthenticationServiceInterface::class),
            $authorization,
        );

        self::assertFalse($context->isGranted('ROLE_ADMIN'));
    }

    public function testIsLoggedDelegatesToTheAuthenticationService(): void
    {
        $authentication = $this->createMock(AuthenticationServiceInterface::class);
        $authentication->expects($this->once())
            ->method('isLogged')
            ->willReturn(true);

        $context = new SecurityRuleContext(
            null,
            null,
            [],
            $authentication,
            $this->createMock(AuthorizationServiceInterface::class),
        );

        self::assertTrue($context->isLogged());
    }

    /**
     * The reason the contract exists.
     *
     * A rule written against it is decided by whatever context the caller supplies, so it runs with
     * no schema, no query and no security services in play, and can be reused anywhere a caller can
     * name a user, a subject and a set of values.
     */
    public function testRuleTypeHintingTheContractRunsAgainstAnyImplementation(): void
    {
        $rule = static fn (SecurityRuleContextInterface $context): bool => $context->isLogged()
            && $context->argument('secret') === 'foo';

        self::assertTrue($rule($this->fakeContext(true, ['secret' => 'foo'])));
        self::assertFalse($rule($this->fakeContext(true, ['secret' => 'bar'])));
        self::assertFalse($rule($this->fakeContext(false, ['secret' => 'foo'])));
    }

    /**
     * A hand-written implementation, deliberately not a mock: it proves the contract asks for
     * nothing a caller outside GraphQLite cannot provide.
     *
     * @param array<string, mixed> $arguments
     */
    private function fakeContext(bool $isLogged, array $arguments): SecurityRuleContextInterface
    {
        return new class ($isLogged, $arguments) implements SecurityRuleContextInterface {
            /** @param array<string, mixed> $arguments */
            public function __construct(
                private readonly bool $isLogged,
                private readonly array $arguments,
            ) {
            }

            public function getUser(): object|null
            {
                return null;
            }

            public function getSource(): object|null
            {
                return null;
            }

            /** @return array<string, mixed> */
            public function getArguments(): array
            {
                return $this->arguments;
            }

            public function isGranted(string $right, mixed $subject = null): bool
            {
                return false;
            }

            public function isLogged(): bool
            {
                return $this->isLogged;
            }

            public function argument(string $name): mixed
            {
                return $this->arguments[$name] ?? null;
            }

            public function hasArgument(string $name): bool
            {
                return array_key_exists($name, $this->arguments);
            }
        };
    }

    /** @param array<string, mixed> $arguments */
    private function context(object|null $user, object|null $source, array $arguments): SecurityRuleContext
    {
        return new SecurityRuleContext(
            $user,
            $source,
            $arguments,
            $this->createMock(AuthenticationServiceInterface::class),
            $this->createMock(AuthorizationServiceInterface::class),
        );
    }
}
