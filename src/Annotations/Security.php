<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Annotations;

use Attribute;
use BadMethodCallException;
use function array_key_exists;
use function gettype;

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
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Security implements MiddlewareAnnotationInterface
{
    /** @var string */
    private $expression;
    /** @var mixed */
    private $failWith;
    /** @var bool */
    private $failWithIsSet = false;
    /** @var int */
    private $statusCode;
    /** @var string */
    private $message;

    /**
     * @param array<string, mixed>|string $data  data array managed by the Doctrine Annotations library or the expression
     * @param mixed $failWith
     *
     * @throws BadMethodCallException
     */
    public function __construct($data = [], string $expression = null, $failWith = '__fail__with__magic__key__', string $message = null, int $statusCode = null)
    {
        if (\is_string($data)) {
            $data = ['expression' => $data];
        } elseif (!\is_array($data)) {
            throw new \TypeError(sprintf('"%s": Argument $data is expected to be a string or array, got "%s".', __METHOD__, gettype($data)));
        }

        $this->expression = $data['value'] ?? $data['expression'] ?? $expression;
        if (!$this->expression) {
            throw new BadMethodCallException('The @Security annotation must be passed an expression. For instance: "@Security("is_granted(\'CAN_EDIT_STUFF\')")"');
        }

        if (array_key_exists('failWith', $data)) {
            $this->failWith = $data['failWith'];
            $this->failWithIsSet = true;
        } elseif ($failWith !== '__fail__with__magic__key__') {
            $this->failWith = $failWith;
            $this->failWithIsSet = true;
        }
        $this->message = $message ?? $data['message'] ?? 'Access denied.';
        $this->statusCode = $statusCode ?? $data['statusCode'] ?? 403;
        if ($this->failWithIsSet === true && (($message || isset($data['message'])) || ($statusCode || isset($data['statusCode'])))) {
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
