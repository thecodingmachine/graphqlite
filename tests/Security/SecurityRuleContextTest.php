<?php

namespace TheCodingMachine\GraphQLite\Security;

use PHPUnit\Framework\TestCase;
use stdClass;

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
