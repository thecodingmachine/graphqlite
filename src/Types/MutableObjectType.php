<?php


namespace TheCodingMachine\GraphQLite\Types;

use Exception;
use GraphQL\Error\InvariantViolation;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\ObjectType;
use TheCodingMachine\GraphQLite\Annotations\Type;
use TheCodingMachine\GraphQLite\Mappers\RecursiveTypeMapperInterface;

/**
 * An object type built from the Type annotation
 */
class MutableObjectType extends ObjectType
{
    // In pending state, we can still add fields.
    public const STATUS_PENDING = 'pending';
    public const STATUS_FROZEN = 'frozen';

    /**
     * @var string
     */
    private $status;

    /**
     * @var array<callable>
     */
    private $fieldsCallables = [];

    /**
     * @var FieldDefinition[]|null
     */
    private $finalFields;

    public function __construct(array $config)
    {
        $this->status = self::STATUS_PENDING;

        parent::__construct($config);
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
        if ($this->status !== self::STATUS_PENDING) {
            throw new \RuntimeException('Tried to add fields to a frozen MutableObjectType.');
        }
        $this->fieldsCallables[] = $fields;
    }

    /**
     * @param string $name
     *
     * @return FieldDefinition
     *
     * @throws Exception
     */
    public function getField($name): FieldDefinition
    {
        if ($this->status === self::STATUS_PENDING) {
            throw new \RuntimeException('You must freeze() a MutableObjectType before fetching its fields.');
        }
        return parent::getField($name);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasField($name): bool
    {
        if ($this->status === self::STATUS_PENDING) {
            throw new \RuntimeException('You must freeze() a MutableObjectType before fetching its fields.');
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
            if ($this->status === self::STATUS_PENDING) {
                throw new \RuntimeException('You must freeze() a MutableObjectType before fetching its fields.');
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
}
