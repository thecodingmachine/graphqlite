<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Annotations;

use BadMethodCallException;
use function array_key_exists;

/**
 * @Annotation
 * @Target({"ANNOTATION", "METHOD"})
 * @Attributes({
 *   @Attribute("expression", type = "string"),
 *   @Attribute("failWith", type = "mixed"),
 *   @Attribute("statusCode", type = "int"),
 *   @Attribute("message", type = "string"),
 * })
 */
class Security implements MiddlewareAnnotationInterface
{
    /** @var string */
    private $expression;
    /** @var mixed */
    private $failWith;
    /** @var bool */
    private $failWithIsSet = false;
    /** @var int|null */
    private $statusCode;
    /** @var string */
    private $message;

    /**
     * @param array<string, mixed> $values
     *
     * @throws BadMethodCallException
     */
    public function __construct(array $values)
    {
        if (! isset($values['value']) && ! isset($values['expression'])) {
            throw new BadMethodCallException('The @Security annotation must be passed an expression. For instance: "@Security("is_granted(\'CAN_EDIT_STUFF\')")"');
        }
        $this->expression = $values['value'] ?? $values['expression'];
        if (array_key_exists('failWith', $values)) {
            $this->failWith = $values['failWith'];
            $this->failWithIsSet = true;
        }
        $this->message = $values['message'] ?? 'Access denied.';
        $this->statusCode = $values['statusCode'] ?? 403;
        if ($this->failWithIsSet === true && (isset($values['message']) || isset($values['statusCode']))) {
            throw new BadMethodCallException('A @Security annotation that has "failWith" attribute set cannot have a message or a statusCode attribute.');
        }
    }

    public function getExpression(): string
    {
        return $this->expression;
    }

    public function isFailWithSet(): bool
    {
        return $this->failWithIsSet;
    }

    /**
     * @return mixed
     */
    public function getFailWith()
    {
        return $this->failWith;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
