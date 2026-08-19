<?php

namespace TheCodingMachine\GraphQLite\Annotations;

use BadMethodCallException;
use PHPUnit\Framework\TestCase;
use TheCodingMachine\GraphQLite\GraphQLRuntimeException;

class SecurityTest extends TestCase
{

    public function testBadParams(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('The #[Security] attribute must be passed an expression or a rule. For instance: "#[Security(rule: [MyRules::class, \'canEditStuff\'])]"');
        new Security([]);
    }

    public function testIncompatibleParams(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('A #[Security] attribute that has "failWith" attribute set cannot have a message or a statusCode attribute.');
        new Security(['expression'=>'foo', 'failWith'=>null, 'statusCode'=>500]);
    }

    public function testExpressionAndRuleAreMutuallyExclusive(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('A #[Security] attribute cannot be passed both an expression and a rule. #[Security] is repeatable: declare one attribute per check.');
        new Security('foo', rule: ['SomeRules', 'canEditStuff']);
    }

    public function testPositionalExpressionStillWorks(): void
    {
        $security = new Security("is_granted('CAN_EDIT_STUFF')");

        self::assertTrue($security->hasExpression());
        self::assertSame("is_granted('CAN_EDIT_STUFF')", $security->getExpression());
        self::assertNull($security->getRule());
    }

    public function testDataValueFormStillWorks(): void
    {
        $security = new Security(['value' => 'foo']);

        self::assertSame('foo', $security->getExpression());
    }

    public function testDataExpressionFormStillWorks(): void
    {
        $security = new Security(['expression' => 'foo', 'message' => 'Nope', 'statusCode' => 401]);

        self::assertSame('foo', $security->getExpression());
        self::assertSame('Nope', $security->getMessage());
        self::assertSame(401, $security->getStatusCode());
    }

    public function testArrayCallableRuleIsReturnedIntact(): void
    {
        $security = new Security(rule: ['SomeRules', 'canEditStuff']);

        self::assertFalse($security->hasExpression());
        self::assertSame(['SomeRules', 'canEditStuff'], $security->getRule());
    }

    public function testInvokableObjectRuleIsReturnedIntact(): void
    {
        $rule = new class {
            public function __invoke(): bool
            {
                return true;
            }
        };
        $security = new Security(rule: $rule);

        self::assertSame($rule, $security->getRule());
    }

    public function testRuleCanBePassedInTheDataArray(): void
    {
        $security = new Security(['rule' => ['SomeRules', 'canEditStuff']]);

        self::assertSame(['SomeRules', 'canEditStuff'], $security->getRule());
    }

    public function testGetExpressionThrowsOnARuleBearingAnnotation(): void
    {
        $security = new Security(rule: ['SomeRules', 'canEditStuff']);

        $this->expectException(GraphQLRuntimeException::class);
        $this->expectExceptionMessage('This #[Security] attribute carries a rule, not an expression. Call getRule() instead, or check hasExpression() first.');
        $security->getExpression();
    }

    public function testFailWithIsUnaffectedByRules(): void
    {
        $security = new Security(rule: ['SomeRules', 'canEditStuff'], failWith: null);

        self::assertTrue($security->isFailWithSet());
        self::assertNull($security->getFailWith());
    }

    /** The message is stored unresolved, but reading it must still yield what it always has. */
    public function testMessageDefaultsToAccessDenied(): void
    {
        $security = new Security('foo');

        self::assertFalse($security->hasMessage());
        self::assertSame('Access denied.', $security->getMessage());
    }

    public function testExplicitMessageIsReportedAsGiven(): void
    {
        $security = new Security('foo', message: 'Nope');

        self::assertTrue($security->hasMessage());
        self::assertSame('Nope', $security->getMessage());
    }

    /**
     * The distinction the feature rests on: a message reading exactly like the default is still a
     * message the field author wrote, so a middleware must not treat it as absent and substitute
     * something of its own.
     */
    public function testMessageWrittenOutAsTheDefaultStillCountsAsGiven(): void
    {
        $security = new Security('foo', message: 'Access denied.');

        self::assertTrue($security->hasMessage());
        self::assertSame('Access denied.', $security->getMessage());
    }

    public function testMessageInTheDataArrayCountsAsGiven(): void
    {
        $security = new Security(['expression' => 'foo', 'message' => 'Nope']);

        self::assertTrue($security->hasMessage());
        self::assertSame('Nope', $security->getMessage());
    }

    public function testFailWithAndMessageAreStillMutuallyExclusive(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('A #[Security] attribute that has "failWith" attribute set cannot have a message or a statusCode attribute.');
        new Security('foo', failWith: null, message: 'Nope');
    }

    public function testFailWithAndDataArrayMessageAreStillMutuallyExclusive(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('A #[Security] attribute that has "failWith" attribute set cannot have a message or a statusCode attribute.');
        new Security(['expression' => 'foo', 'failWith' => null, 'message' => 'Nope']);
    }

    /**
     * failWith on its own has never been an error, and the message it reports has always been the
     * default. Storing the message unresolved must not turn either of those into a change.
     */
    public function testFailWithAloneIsStillAccepted(): void
    {
        $security = new Security('foo', failWith: null);

        self::assertTrue($security->isFailWithSet());
        self::assertFalse($security->hasMessage());
        self::assertSame('Access denied.', $security->getMessage());
    }

    /**
     * Pins the exclusion's exact trigger. It tests the raw argument for truthiness, so an empty
     * message has never fired it, quirk and all. Testing the stored message instead would have
     * silently widened the rule, since an empty string is a message that was written.
     */
    public function testFailWithAndAnEmptyMessageAreStillAccepted(): void
    {
        $security = new Security('foo', failWith: null, message: '');

        self::assertTrue($security->isFailWithSet());
        self::assertTrue($security->hasMessage());
        self::assertSame('', $security->getMessage());
    }
}
