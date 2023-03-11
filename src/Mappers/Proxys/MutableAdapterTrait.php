<?php

namespace TheCodingMachine\GraphQLite\Mappers\Proxys;

use Exception;
use GraphQL\Error\InvariantViolation;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\ObjectType;
use RuntimeException;
use TheCodingMachine\GraphQLite\Types\MutableInterface;
use TheCodingMachine\GraphQLite\Types\NoFieldsException;

/**
 * @internal
 */
trait MutableAdapterTrait
{
    private ObjectType|InterfaceType $type;
    private ?string $className = null;
    private string $status = MutableInterface::STATUS_PENDING;

    /** @var array<callable> */
    private array $fieldsCallables = [];

    /** @var array<string,FieldDefinition>|null */
    private ?array $finalFields = null;

    /**
     * @throws InvariantViolation
     */
    public function assertValid(): void
    {
        $this->type->assertValid();
    }

    public function jsonSerialize(): string
    {
        return $this->type->jsonSerialize();
    }

    public function toString(): string
    {
        return $this->type->toString();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->type->__toString();
    }

    /**
     * @throws Exception
     */
    public function getField(string $name): FieldDefinition
    {
        if ($this->status === MutableInterface::STATUS_PENDING) {
            throw new RuntimeException('You must freeze() a ' . static::class . ' before fetching its fields.');
        }

        return $this->type->getField($name);
    }

    public function hasField(string $name): bool
    {
        if ($this->status === MutableInterface::STATUS_PENDING) {
            throw new RuntimeException('You must freeze() a ' . static::class . ' before fetching its fields.');
        }

        return $this->type->hasField($name);
    }

    /**
     * @return array<string,FieldDefinition>
     *
     * @throws InvariantViolation
     */
    public function getFields(): array
    {
        if ($this->finalFields === null) {
            if ($this->status === MutableInterface::STATUS_PENDING) {
                throw new RuntimeException('You must freeze() a ' . static::class . ' before fetching its fields.');
            }

            $this->finalFields = $this->type->getFields();
            foreach ($this->fieldsCallables as $fieldsCallable) {
                /**
                 * @var FieldDefinition[] $fields
                 */
                $fields = FieldDefinition::defineFieldMap($this, $fieldsCallable()) + $this->finalFields;

                $this->finalFields = $fields;
            }
            if (empty($this->finalFields)) {
                throw NoFieldsException::create($this->name);
            }
        }

        return $this->finalFields;
    }

    public function freeze(): void
    {
        $this->status = self::STATUS_FROZEN;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function addFields(callable $fields): void
    {
        if ($this->status !== MutableInterface::STATUS_PENDING) {
            throw new RuntimeException('Tried to add fields to a frozen MutableInterfaceType.');
        }
        $this->fieldsCallables[] = $fields;
    }
}
