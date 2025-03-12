<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Annotations;

use Attribute;
use BadMethodCallException;

use function array_key_exists;
use function is_string;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Security implements MiddlewareAnnotationInterface
{
    private string $expression;
    private mixed $failWith;
    private bool $failWithIsSet = false;
    private int $statusCode;
    private string $message;

    /**
     * @param array<string, mixed>|string $data  data array managed by the Doctrine Annotations library or the expression
     *
     * @throws BadMethodCallException
     */
    public function __construct(
        array|string $data = [],
        string|null $expression = null,
        mixed $failWith = '__fail__with__magic__key__',
        string|null $message = null,
        int|null $statusCode = null,
    ) {
        if (is_string($data)) {
            $data = ['expression' => $data];
        }

        $expression = $data['value'] ?? $data['expression'] ?? $expression;
        if (! $expression) {
            throw new BadMethodCallException('The #[Security] attribute must be passed an expression. For instance: "#[Security("is_granted(\'CAN_EDIT_STUFF\')")]"');
        }

        $this->expression = $expression;

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
            throw new BadMethodCallException('A #[Security] attribute that has "failWith" attribute set cannot have a message or a statusCode attribute.');
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

    public function getFailWith(): mixed
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
