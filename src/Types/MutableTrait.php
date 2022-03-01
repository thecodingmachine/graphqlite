<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Types;

use Exception;
use GraphQL\Error\InvariantViolation;
use GraphQL\Type\Definition\FieldDefinition;
use RuntimeException;
use Webmozart\Assert\Assert;

use function array_keys;
use function array_merge;
use function array_unique;

trait MutableTrait
{
    /** @var string */
    private $status;

    /** @var array<callable> */
    private $fieldsCallables = [];

    /** @var FieldDefinition[]|null */
    private $fields;
    /** @var class-string<object>|null */
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
    public function getField(string $name): FieldDefinition
    {
        $this->initializeFields();

        return $this->fields[$name] ?? parent::getField($name);
    }

    public function findField(string $name): ?FieldDefinition
    {
        $this->initializeFields();

        return $this->fields[$name] ?? parent::findField($name);
    }

    /**
     * @param string $name
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint
     */
    public function hasField($name): bool
    {
        $this->initializeFields();

        return isset($this->fields[$name]) || parent::hasField($name);
    }

    /**
     * @return FieldDefinition[]
     *
     * @throws InvariantViolation
     */
    public function getFields(): array
    {
        $this->initializeFields();
        Assert::isArray($this->fields);

        return array_merge(parent::getFields(), $this->fields);
    }

    /**
     * @return string[]
     */
    public function getFieldNames(): array
    {
        $this->initializeFields();
        Assert::isArray($this->fields);

        return array_unique(array_merge(parent::getFieldNames(), array_keys($this->fields)));
    }

    /**
     * Returns the PHP class mapping this GraphQL type (if any)
     *
     * @return class-string<object>|null
     */
    public function getMappedClassName(): ?string
    {
        return $this->className;
    }

    private function initializeFields(): void
    {
        if ($this->status === MutableInterface::STATUS_PENDING) {
            throw new RuntimeException(
                'You must freeze() the MutableObjectType, ' . $this->className . ', before fetching its fields.'
            );
        }

        if (isset($this->fields)) {
            return;
        }

        $this->fields = [];
        foreach ($this->fieldsCallables as $fieldsCallable) {
            $this->fields = FieldDefinition::defineFieldMap($this, $fieldsCallable()) + $this->fields;
        }

        if (empty($this->fields) && empty(parent::getFieldNames())) {
            throw NoFieldsException::create($this->name);
        }
    }
}
