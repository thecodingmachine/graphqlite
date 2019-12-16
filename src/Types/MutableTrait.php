<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Types;

use Exception;
use GraphQL\Error\InvariantViolation;
use GraphQL\Type\Definition\FieldDefinition;
use RuntimeException;

trait MutableTrait
{
    /** @var string */
    private $status;

    /** @var array<callable> */
    private $fieldsCallables = [];

    /** @var FieldDefinition[]|null */
    private $finalFields;
    /** @var string|null */
    private $className;

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
            throw new RuntimeException('Tried to add fields to a frozen MutableObjectType.');
        }
        $this->fieldsCallables[] = $fields;
    }

    /**
     * @param string $name
     *
     * @throws Exception
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint
     */
    public function getField($name): FieldDefinition
    {
        if ($this->status === MutableInterface::STATUS_PENDING) {
            throw new RuntimeException('You must freeze() a MutableObjectType before fetching its fields.');
        }

        return parent::getField($name);
    }

    /**
     * @param string $name
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint
     */
    public function hasField($name): bool
    {
        if ($this->status === MutableInterface::STATUS_PENDING) {
            throw new RuntimeException('You must freeze() a MutableObjectType before fetching its fields.');
        }

        return parent::hasField($name);
    }

    /**
     * @return FieldDefinition[]
     *
     * @throws InvariantViolation
     */
    public function getFields(): array
    {
        if ($this->finalFields === null) {
            if ($this->status === MutableInterface::STATUS_PENDING) {
                throw new RuntimeException('You must freeze() a MutableObjectType before fetching its fields.');
            }

            $this->finalFields = parent::getFields();
            foreach ($this->fieldsCallables as $fieldsCallable) {
                $this->finalFields = FieldDefinition::defineFieldMap($this, $fieldsCallable()) + $this->finalFields;
            }
            if (empty($this->finalFields)) {
                throw NoFieldsException::create($this->name);
            }
        }

        return $this->finalFields;
    }

    /**
     * Returns the PHP class mapping this GraphQL type (if any)
     */
    public function getMappedClassName(): ?string
    {
        return $this->className;
    }
}
