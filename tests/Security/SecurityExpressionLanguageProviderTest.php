<?php

namespace TheCodingMachine\GraphQLite\Security;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\NullAdapter;
use Symfony\Component\Cache\Adapter\Psr16Adapter;
use Symfony\Component\Cache\Simple\NullCache;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class SecurityExpressionLanguageProviderTest extends TestCase
{
    public function testIsGranted(): void
    {
        $expressionLanguage = new ExpressionLanguage(new NullAdapter(), [new SecurityExpressionLanguageProvider()]);
        $php = $expressionLanguage->compile('is_granted("Foo")');
        $this->assertSame('$authorizationService->isAllowed("Foo", null)', $php);
    }

    public function testIsLogged(): void
    {
        $expressionLanguage = new ExpressionLanguage(new NullAdapter(), [new SecurityExpressionLanguageProvider()]);
        $php = $expressionLanguage->compile('is_logged()');
        $this->assertSame('$authenticationService->isLogged()', $php);
    }
}
