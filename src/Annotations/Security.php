<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Annotations;

use Attribute;
use BadMethodCallException;
use TheCodingMachine\GraphQLite\GraphQLRuntimeException;

use function array_key_exists;
use function is_string;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Security implements MiddlewareAnnotationInterface
{
    private string|null $expression = null;

    /** @var array{class-string, string}|object|null */
    private array|object|null $rule = null;

    private mixed $failWith;
    private bool $failWithIsSet = false;
    private int $statusCode;

    /**
     * The message exactly as it was written in the attribute, or null when none was written.
     *
     * The default is applied by {@see getMessage()} rather than here, so that "no message given"
     * stays distinguishable from "a message was given, and it happens to read like the default".
     * A middleware with a better message available, such as one the rule states itself, can only
     * prefer it over the default if it can tell those two apart. See {@see hasMessage()}.
     */
    private string|null $message;

    /**
     * @param array<string, mixed>|string $data data array managed by the Doctrine Annotations library or the expression
     * @param string|null $expression A security expression, evaluated by Symfony ExpressionLanguage. Suits short,
     *                                local predicates. Prefer $rule for logic worth typing, testing or reusing.
     * @param array{class-string, string}|object|null $rule A callable receiving a
     *                                                      {@see \TheCodingMachine\GraphQLite\Security\SecurityRuleContext}
     *                                                      and returning bool. Accepts an array callable, an invokable
     *                                                      object, and — from PHP 8.5 — first-class callable syntax
     *                                                      or an inline static closure. A non-static method named by
     *                                                      the array form is resolved through the container.
     *
     * @throws BadMethodCallException
     */
    public function __construct(
        array|string $data = [],
        string|null $expression = null,
        mixed $failWith = '__fail__with__magic__key__',
        string|null $message = null,
        int|null $statusCode = null,
        array|object|null $rule = null,
    ) {
        if (is_string($data)) {
            $data = ['expression' => $data];
        }

        $expression = $data['value'] ?? $data['expression'] ?? $expression;
        $rule = $data['rule'] ?? $rule;

        // An empty expression has always counted as "no expression"; keep that.
        if (! $expression) {
            $expression = null;
        }

        // Silent precedence would turn a half-finished migration into a security change: whichever
        // of the two lost would simply stop being enforced, with nothing in the schema or the
        // response saying so. #[Security] is repeatable, so two checks means two attributes.
        if ($expression !== null && $rule !== null) {
            throw new BadMethodCallException('A #[Security] attribute cannot be passed both an expression and a rule. #[Security] is repeatable: declare one attribute per check.');
        }

        if ($expression === null && $rule === null) {
            throw new BadMethodCallException('The #[Security] attribute must be passed an expression or a rule. For instance: "#[Security(rule: [MyRules::class, \'canEditStuff\'])]"');
        }

        $this->expression = $expression;
        $this->rule = $rule;

        if (array_key_exists('failWith', $data)) {
            $this->failWith = $data['failWith'];
            $this->failWithIsSet = true;
        } elseif ($failWith !== '__fail__with__magic__key__') {
            $this->failWith = $failWith;
            $this->failWithIsSet = true;
        }
        $this->message = $message ?? $data['message'] ?? null;
        $this->statusCode = $statusCode ?? $data['statusCode'] ?? 403;
        // Deliberately reads the raw arguments rather than $this->message: an attribute passing
        // failWith together with a message has always been an error, and storing the message
        // unresolved must not change which combinations are rejected.
        if ($this->failWithIsSet === true && (($message || isset($data['message'])) || ($statusCode || isset($data['statusCode'])))) {
            throw new BadMethodCallException('A #[Security] attribute that has "failWith" attribute set cannot have a message or a statusCode attribute.');
        }
    }

    /**
     * Whether this annotation carries an expression rather than a rule.
     *
     * Custom middlewares reading #[Security] annotations should branch on this, or on
     * {@see getRule()}, before calling {@see getExpression()}.
     */
    public function hasExpression(): bool
    {
        return $this->expression !== null;
    }

    /** @throws GraphQLRuntimeException When this annotation carries a rule instead of an expression. */
    public function getExpression(): string
    {
        if ($this->expression === null) {
            throw new GraphQLRuntimeException('This #[Security] attribute carries a rule, not an expression. Call getRule() instead, or check hasExpression() first.');
        }

        return $this->expression;
    }

    /**
     * The callable to invoke, or null when this annotation carries an expression.
     *
     * The value is returned exactly as it was written in the attribute; turning it into a Closure
     * is the middleware's job, so that it happens once, at schema-build time.
     *
     * @return array{class-string, string}|object|null
     */
    public function getRule(): array|object|null
    {
        return $this->rule;
    }

    public function isFailWithSet(): bool
    {
        return $this->failWithIsSet;
    }

    public function getFailWith(): mixed
    {
        return $this->failWith;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Whether a message was written in the attribute.
     *
     * A middleware holding a better message than the default, such as one a rule states itself,
     * branches on this: an explicit message is the field author's decision and always wins, while
     * an absent one leaves the middleware free to supply its own before falling back to
     * {@see getMessage()}.
     */
    public function hasMessage(): bool
    {
        return $this->message !== null;
    }

    /**
     * The message to deny with, defaulting to "Access denied." when the attribute gave none.
     *
     * Always a string, so a middleware that has nothing better to offer can call this alone.
     */
    public function getMessage(): string
    {
        return $this->message ?? 'Access denied.';
    }
}
