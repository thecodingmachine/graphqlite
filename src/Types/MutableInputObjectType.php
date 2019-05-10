<?php


namespace TheCodingMachine\GraphQLite\Types;

use Exception;
use GraphQL\Error\InvariantViolation;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\InputObjectField;
use GraphQL\Type\Definition\InputObjectType;

/**
 * An input object type built from the Factory annotation.
 * It can be later extended with the "Decorate" annotation
 */
class MutableInputObjectType extends InputObjectType implements MutableInputInterface
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
     * @var InputObjectField[]|null
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
            throw new \RuntimeException('Tried to add fields to a frozen MutableInputObjectType.');
        }
        $this->fieldsCallables[] = $fields;
    }

    /**
     * @param string $name
     *
     * @return InputObjectField
     *
     * @throws Exception
     */
    public function getField($name): InputObjectField
    {
        if ($this->status === self::STATUS_PENDING) {
            throw new \RuntimeException('You must freeze() a MutableInputObjectType before fetching its fields.');
        }
        return parent::getField($name);
    }

    /**
     * @return InputObjectField[]
     *
     * @throws InvariantViolation
     */
    public function getFields(): array
    {
        if ($this->finalFields === null) {
            if ($this->status === self::STATUS_PENDING) {
                throw new \RuntimeException('You must freeze() a MutableInputObjectType before fetching its fields.');
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
